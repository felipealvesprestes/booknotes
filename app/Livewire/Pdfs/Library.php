<?php

namespace App\Livewire\Pdfs;

use App\Models\Log;
use App\Models\PdfDocument;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Library extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public ?int $selectedPdfId = null;

    public $upload = null;
    public string $title = '';
    public bool $showUploadSuccess = false;

    protected array $perPageOptions = [10, 25, 50];

    protected $queryString = [
        'search'  => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    protected $rules = [
        'title'  => ['nullable', 'string', 'max:255'],
        'upload' => ['nullable', 'file', 'mimes:pdf', 'max:20480'], // 20MB
    ];

    public function mount(): void
    {
        if (! $this->selectedPdfId) {
            $this->selectedPdfId = auth()->user()
                ?->pdfDocuments()
                ->latest('last_opened_at')
                ->latest()
                ->value('id');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $perPage = (int) $value;

        if (! in_array($perPage, $this->perPageOptions, true)) {
            $perPage = $this->perPageOptions[0];
        }

        $this->perPage = $perPage;

        $this->resetPage();
    }

    public function updatedUpload(): void
    {
        $this->showUploadSuccess = false;
        $this->validateOnly('upload');

        if ($this->upload && blank($this->title)) {
            $this->title = pathinfo($this->upload->getClientOriginalName(), PATHINFO_FILENAME);
        }
    }

    public function save(): void
    {
        $this->validate([
            'title'  => ['nullable', 'string', 'max:255'],
            'upload' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $file = $this->upload;

        $storedPath = $file->store('pdfs/' . auth()->id(), 'local');

        $pdfDocument = PdfDocument::create([
            'user_id' => auth()->id(),
            'title' => $this->title ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_name' => $file->getClientOriginalName(),
            'path' => $storedPath,
            'size' => $file->getSize(),
            'last_opened_at' => now(),
        ]);

        Log::create([
            'action'  => 'pdf.uploaded',
            'context' => [
                'pdf_id' => $pdfDocument->id,
                'title'  => $pdfDocument->title,
            ],
        ]);

        $this->reset(['upload', 'title']);
        $this->showUploadSuccess = true;
        $this->selectedPdfId = $pdfDocument->id;
        $this->resetPage();

        session()->flash('status', __('PDF uploaded successfully.'));
    }

    public function selectPdf(int $pdfId): void
    {
        $pdf = PdfDocument::where('user_id', auth()->id())
            ->findOrFail($pdfId);

        PdfDocument::withoutTimestamps(function () use ($pdf): void {
            $pdf->forceFill(['last_opened_at' => now()])->save();
        });

        $this->selectedPdfId = $pdf->id;
    }

    public function deletePdf(int $pdfId): void
    {
        $pdf = PdfDocument::where('user_id', auth()->id())
            ->findOrFail($pdfId);

        Storage::disk('local')->delete($pdf->path);
        $pdf->delete();

        Log::create([
            'action'  => 'pdf.deleted',
            'context' => [
                'pdf_id' => $pdfId,
                'title'  => $pdf->title,
            ],
        ]);

        if ($this->selectedPdfId === $pdfId) {
            $this->selectedPdfId = null;
        }

        $this->resetPage();

        session()->flash('status', __('PDF deleted successfully.'));
    }

    public function render(): View
    {
        $pdfs = PdfDocument::query()
            ->where('user_id', auth()->id())
            ->latest('last_opened_at')
            ->latest()
            ->when($this->search, function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery
                        ->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('original_name', 'like', '%' . $this->search . '%');
                });
            })
            ->paginate($this->perPage);

        $selectedPdf = null;

        if ($this->selectedPdfId) {
            $selectedPdf = PdfDocument::where('user_id', auth()->id())
                ->find($this->selectedPdfId);

            if (! $selectedPdf) {
                $this->selectedPdfId = null;
            }
        }

        if (! $selectedPdf && $pdfs->isNotEmpty()) {
            $selectedPdf = $pdfs->first();
            $this->selectedPdfId = $selectedPdf->id;
        }

        if ($pdfs->isEmpty()) {
            $selectedPdf = null;
            $this->selectedPdfId = null;
        }

        return view('livewire.pdfs.library', [
            'pdfs'           => $pdfs,
            'selectedPdf'    => $selectedPdf,
            'perPageOptions' => $this->perPageOptions,
        ])->layout('layouts.app', [
            'title' => __('Document library'),
        ]);
    }
}
