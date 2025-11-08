<?php

namespace App\Livewire\Disciplines;

use App\Models\Discipline;
use App\Models\Log;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public $notebookFilter = null;

    /**
     * Reset page when filters change.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingNotebookFilter(): void
    {
        $this->resetPage();
    }

    public function deleteDiscipline(int $disciplineId): void
    {
        $discipline = Discipline::with('notebook')->findOrFail($disciplineId);

        $context = [
            'discipline_id' => $discipline->id,
            'title' => $discipline->title,
            'notebook_id' => $discipline->notebook_id,
        ];

        $discipline->delete();

        Log::create([
            'action' => 'discipline.deleted',
            'context' => $context,
        ]);

        session()->flash('status', __('Discipline deleted successfully.'));

        if ($this->getPage() > 1 && $this->pageIsEmpty()) {
            $this->previousPage();
        }
    }

    protected function pageIsEmpty(): bool
    {
        return $this->getDisciplinesProperty()->count() === 0;
    }

    public function getDisciplinesProperty()
    {
        return Discipline::query()
            ->with('notebook')
            ->latest()
            ->when($this->search, fn ($query) => $query->where('title', 'like', '%' . $this->search . '%'))
            ->when($this->notebookFilter, fn ($query) => $query->where('notebook_id', $this->notebookFilter))
            ->paginate(10);
    }

    public function render(): View
    {
        $notebooks = auth()->user()->notebooks()->select('id', 'title')->orderBy('title')->get();

        return view('livewire.disciplines.index', [
            'disciplines' => $this->disciplines,
            'notebooks' => $notebooks,
        ])->layout('layouts.app', [
            'title' => __('Disciplines'),
        ]);
    }
}
