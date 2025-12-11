<?php

namespace App\Livewire\Study;

use App\Exceptions\AiFlashcardGenerationException;
use App\Exceptions\AiFlashcardsLimitException;
use App\Exceptions\PdfImportException;
use App\Models\Discipline;
use App\Services\Ai\AiFlashcardStorageService;
use App\Services\Ai\AiFlashcardsUsageService;
use App\Services\Ai\GenerateFlashcardsService;
use App\Services\Pdf\PdfTextExtractor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class PdfFlashcards extends Component
{
    use WithFileUploads;

    public ?int $disciplineId = null;

    public int $pdfQuantity = 20;

    public $pdfUpload = null;

    public bool $isProcessing = false;

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    public int $processedPages = 0;

    public int $maxPages = 30;

    public int $maxCharacters = 8000;

    public int $maxUploadKilobytes = 20480;

    /** @var array<int, int> */
    public array $quantityOptions = [];

    public int $dailyLimit = 0;

    public int $usedToday = 0;

    public int $remainingToday = 0;

    public function mount(): void
    {
        $this->quantityOptions = $this->resolveQuantityOptions();
        $this->pdfQuantity = $this->quantityOptions[0] ?? 20;
        $this->maxPages = (int) config('ai.flashcards.pdf.max_pages', 30);
        $this->maxCharacters = (int) config('ai.flashcards.pdf.max_characters', 8000);
        $this->maxUploadKilobytes = (int) config('ai.flashcards.pdf.max_upload_kb', 20480);

        $this->setDefaultDiscipline();
        $this->reloadUsage(adjustQuantity: true);
    }

    public function updatedPdfUpload(): void
    {
        $this->resetMessages();
    }

    public function updatedPdfQuantity($value): void
    {
        $quantity = (int) $value;

        if (! in_array($quantity, $this->quantityOptions, true)) {
            $this->pdfQuantity = $this->quantityOptions[0] ?? 20;
        }
    }

    public function generateFromPdf(): void
    {
        $this->resetMessages();
        $this->isProcessing = true;

        try {
            $this->validate($this->rules(), [], $this->validationAttributes());
        } catch (ValidationException $exception) {
            $this->isProcessing = false;

            throw $exception;
        }

        $user = Auth::user();

        if (! $user) {
            $this->finishProcessing();

            return;
        }

        $discipline = Discipline::ownedBy($user)
            ->find($this->disciplineId);

        if (! $discipline) {
            $this->addError('disciplineId', __('pdf_flashcards.errors.invalid_discipline'));
            $this->finishProcessing();

            return;
        }

        try {
            $this->usageService()->ensureWithinLimit($user, $this->pdfQuantity);
        } catch (AiFlashcardsLimitException $exception) {
            $this->handleLimitError($exception);
            $this->finishProcessing();

            return;
        }

        $path = $this->pdfUpload?->getRealPath();

        if (! $path || ! file_exists($path)) {
            $this->errorMessage = __('pdf_flashcards.errors.parse_failed');
            $this->addError('pdfUpload', $this->errorMessage);
            $this->finishProcessing();

            return;
        }

        try {
            $extraction = $this->pdfExtractor()->extract(
                $path,
                $this->maxPages,
                $this->maxCharacters,
            );
        } catch (PdfImportException $exception) {
            $this->errorMessage = $exception->getMessage();
            $this->addError('pdfUpload', $exception->getMessage());
            $this->finishProcessing();

            return;
        } catch (\Throwable) {
            $this->errorMessage = __('pdf_flashcards.errors.parse_failed');
            $this->addError('pdfUpload', $this->errorMessage);
            $this->finishProcessing();

            return;
        }

        try {
            $flashcards = $this->generatorService()->generateFromContent(
                $user,
                $discipline,
                $extraction['text'],
                $this->pdfQuantity,
            );
        } catch (AiFlashcardGenerationException $exception) {
            $this->errorMessage = $exception->getMessage();
            $this->finishProcessing();

            return;
        }

        $created = $this->storageService()->store($discipline, $flashcards, 'pdf_flashcards');

        if ($created === 0) {
            $this->errorMessage = __('ai_flashcards.save_error');
            $this->finishProcessing();

            return;
        }

        $this->usageService()->increment($user, $created);
        $this->reloadUsage(adjustQuantity: true);

        $this->processedPages = $extraction['pages'] ?? 0;
        $this->statusMessage = trans_choice('pdf_flashcards.status.generated', $created, [
            'count' => $created,
            'discipline' => $discipline->title,
        ]);

        $this->finishProcessing(clearUpload: true);
    }

    public function render(): View
    {
        $disciplines = Discipline::ownedBy(Auth::user())
            ->orderBy('title')
            ->get();

        if (! $this->disciplineId && $disciplines->isNotEmpty()) {
            $this->disciplineId = $disciplines->first()->id;
        }

        return view('livewire.study.pdf-flashcards', [
            'disciplines' => $disciplines,
        ])->layout('layouts.app', [
            'title' => __('pdf_flashcards.title'),
        ]);
    }

    protected function rules(): array
    {
        return [
            'pdfUpload' => [
                'required',
                'file',
                'mimes:pdf',
                'max:' . $this->maxUploadKilobytes,
            ],
            'disciplineId' => [
                'required',
                'integer',
                Rule::exists('disciplines', 'id')->where(function ($query) {
                    $query->where('user_id', Auth::id());
                }),
            ],
            'pdfQuantity' => [
                'required',
                'integer',
                Rule::in($this->quantityOptions),
            ],
        ];
    }

    protected function resolveQuantityOptions(): array
    {
        $options = config('ai.flashcards.pdf.quantities', [20, 30, 50]);

        return collect($options)
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    protected function reloadUsage(bool $adjustQuantity = false): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $usageService = $this->usageService();
        $usage = $usageService->getUsageForToday($user);

        $this->dailyLimit = $usageService->getDailyLimit();
        $this->usedToday = (int) $usage->generated_count;
        $this->remainingToday = max(0, $this->dailyLimit - $this->usedToday);

        if ($adjustQuantity) {
            $this->pdfQuantity = $this->suggestQuantity();
        }
    }

    protected function suggestQuantity(): int
    {
        $remaining = $this->remainingToday;
        $options = $this->quantityOptions;

        if (empty($options)) {
            return 20;
        }

        $minimum = $options[0];

        if ($remaining <= 0) {
            return $minimum;
        }

        foreach ($options as $option) {
            if ($remaining >= $option) {
                return $option;
            }
        }

        return $minimum;
    }

    protected function setDefaultDiscipline(): void
    {
        if ($this->disciplineId) {
            return;
        }

        $this->disciplineId = Discipline::ownedBy(Auth::user())
            ->orderBy('title')
            ->value('id');
    }

    protected function handleLimitError(AiFlashcardsLimitException $exception): void
    {
        $remaining = $exception->remaining();

        $message = $remaining > 0
            ? trans_choice(
                'ai_flashcards.limit_remaining',
                $remaining,
                ['count' => $remaining],
            )
            : __('ai_flashcards.limit_reached');

        $this->addError('pdfQuantity', $message);
        $this->reloadUsage(adjustQuantity: true);
    }

    protected function validationAttributes(): array
    {
        return [
            'pdfUpload' => __('pdf_flashcards.fields.pdf'),
            'disciplineId' => __('pdf_flashcards.fields.discipline'),
            'pdfQuantity' => __('pdf_flashcards.fields.quantity'),
        ];
    }

    protected function usageService(): AiFlashcardsUsageService
    {
        return app(AiFlashcardsUsageService::class);
    }

    protected function generatorService(): GenerateFlashcardsService
    {
        return app(GenerateFlashcardsService::class);
    }

    protected function pdfExtractor(): PdfTextExtractor
    {
        return app(PdfTextExtractor::class);
    }

    protected function storageService(): AiFlashcardStorageService
    {
        return app(AiFlashcardStorageService::class);
    }

    protected function finishProcessing(bool $clearUpload = true): void
    {
        $this->isProcessing = false;

        if ($clearUpload) {
            $this->cleanupUpload();
        }
    }

    protected function cleanupUpload(): void
    {
        if (! $this->pdfUpload) {
            return;
        }

        $path = $this->pdfUpload->getRealPath();

        if ($path && file_exists($path)) {
            @unlink($path);
        }

        $this->pdfUpload = null;
    }

    protected function resetMessages(): void
    {
        $this->statusMessage = null;
        $this->errorMessage = null;
        $this->processedPages = 0;
        $this->resetErrorBag();
    }
}
