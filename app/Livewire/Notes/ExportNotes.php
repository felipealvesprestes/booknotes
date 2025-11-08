<?php

namespace App\Livewire\Notes;

use App\Jobs\GenerateNoteExportPdf;
use App\Models\Discipline;
use App\Models\Notebook;
use App\Models\NoteExport;
use App\Support\NoteExportBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class ExportNotes extends Component
{
    public string $scope = 'all';

    public ?int $selectedNotebook = null;

    public ?int $selectedDiscipline = null;

    public string $noteType = 'all';

    public string $layoutGrouping = 'discipline';

    public string $layoutOrientation = 'portrait';

    public string $layoutDensity = 'detailed';

    public bool $includeFlashcardAnswer = true;

    public bool $includeNoteBody = true;

    public ?int $recentExportId = null;

    protected array $scopeOptions = ['all', 'notebook', 'discipline'];

    protected array $noteTypeOptions = ['all', 'notes', 'flashcards'];

    protected array $layoutGroupingOptions = ['discipline', 'notebook'];

    protected array $layoutOrientationOptions = ['portrait', 'landscape'];

    protected array $layoutDensityOptions = ['detailed', 'compact'];

    protected function normalizeBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $normalized ?? false;
    }

    public function updatedScope(string $value): void
    {
        if (! in_array($value, $this->scopeOptions, true)) {
            $this->scope = 'all';
        }

        if ($this->scope === 'all') {
            $this->selectedNotebook = null;
            $this->selectedDiscipline = null;
        } elseif ($this->scope === 'notebook') {
            $this->selectedDiscipline = null;
        } elseif ($this->scope === 'discipline') {
            $this->selectedNotebook = null;
        }
    }

    public function updatedSelectedNotebook($value): void
    {
        if ($value) {
            $this->selectedNotebook = (int) $value;
            $this->selectedDiscipline = null;
            $this->scope = 'notebook';
        } else {
            $this->selectedNotebook = null;
            if ($this->scope === 'notebook') {
                $this->scope = $this->selectedDiscipline ? 'discipline' : 'all';
            }
        }
    }

    public function updatedSelectedDiscipline($value): void
    {
        if ($value) {
            $this->selectedDiscipline = (int) $value;
            $this->selectedNotebook = null;
            $this->scope = 'discipline';
        } else {
            $this->selectedDiscipline = null;
            if ($this->scope === 'discipline') {
                $this->scope = $this->selectedNotebook ? 'notebook' : 'all';
            }
        }
    }

    public function updatedNoteType($value): void
    {
        if (! in_array($value, $this->noteTypeOptions, true)) {
            $this->noteType = 'all';
        }
    }

    public function updatedLayoutGrouping($value): void
    {
        if (! in_array($value, $this->layoutGroupingOptions, true)) {
            $this->layoutGrouping = 'discipline';
        }
    }

    public function updatedLayoutOrientation($value): void
    {
        if (! in_array($value, $this->layoutOrientationOptions, true)) {
            $this->layoutOrientation = 'portrait';
        }
    }

    public function updatedLayoutDensity($value): void
    {
        if (! in_array($value, $this->layoutDensityOptions, true)) {
            $this->layoutDensity = 'detailed';
        }
    }

    public function updatedIncludeFlashcardAnswer($value): void
    {
        $this->includeFlashcardAnswer = $this->normalizeBoolean($value);
    }

    public function updatedIncludeNoteBody($value): void
    {
        $this->includeNoteBody = $this->normalizeBoolean($value);
    }

    public function export(): void
    {
        if ($this->scope === 'notebook' && ! $this->selectedNotebook) {
            $this->addError('export', __('Select a notebook before exporting.'));

            return;
        }

        if ($this->scope === 'discipline' && ! $this->selectedDiscipline) {
            $this->addError('export', __('Select a discipline before exporting.'));

            return;
        }

        $config = $this->exportConfiguration();
        $notes = NoteExportBuilder::collectNotes($config, auth()->user());

        if ($notes->isEmpty()) {
            $this->addError('export', __('Select at least one note or flashcard to export.'));

            return;
        }

        $fileName = sprintf('booknotes-export-%s.pdf', now()->format('Ymd-His'));

        $noteExport = NoteExport::create([
            'user_id' => auth()->id(),
            'file_name' => $fileName,
            'filters' => $config,
            'status' => NoteExport::STATUS_PENDING,
        ]);

        GenerateNoteExportPdf::dispatch($noteExport->id);

        $this->recentExportId = $noteExport->id;

        $noteExport->refresh();
    }

    protected function notebooks(): Collection
    {
        return Notebook::query()
            ->with([
                'disciplines' => fn ($query) => $query
                    ->withCount('notes')
                    ->withCount([
                        'notes as flashcard_count' => fn ($countQuery) => $countQuery->where('is_flashcard', true),
                    ]),
            ])
            ->orderBy('title')
            ->get();
    }

    protected function disciplines(): Collection
    {
        $query = Discipline::query()
            ->with('notebook')
            ->withCount('notes')
            ->withCount([
                'notes as flashcard_count' => fn ($countQuery) => $countQuery->where('is_flashcard', true),
            ])
            ->orderBy('title');

        if ($this->scope === 'notebook' && $this->selectedNotebook) {
            $query->where('notebook_id', $this->selectedNotebook);
        }

        return $query->get();
    }

    public function render(): View
    {
        $user = auth()->user();
        $config = $this->exportConfiguration();

        $notes = NoteExportBuilder::collectNotes($config, $user);
        $summary = NoteExportBuilder::summary($notes);

        $recentExports = NoteExport::query()
            ->ownedBy($user)
            ->recent()
            ->take(5)
            ->get();

        $focusedExport = $this->recentExportId
            ? $recentExports->firstWhere('id', $this->recentExportId)
            : null;

        return view('livewire.notes.export-notes', [
            'notebooks' => $this->notebooks(),
            'disciplines' => $this->disciplines(),
            'summary' => $summary,
            'previewGroups' => NoteExportBuilder::groupNotes(
                $notes->take(3),
                $config['layoutGrouping'] ?? 'discipline'
            ),
            'filters' => $config,
            'recentExports' => $recentExports,
            'focusedExport' => $focusedExport,
        ])->layout('layouts.app', [
            'title' => __('Export notes to PDF'),
        ]);
    }

    protected function exportConfiguration(): array
    {
        return [
            'scope' => $this->scope,
            'selectedNotebook' => $this->selectedNotebook,
            'selectedDiscipline' => $this->selectedDiscipline,
            'noteType' => $this->noteType,
            'layoutGrouping' => $this->layoutGrouping,
            'layoutOrientation' => $this->layoutOrientation,
            'layoutDensity' => $this->layoutDensity,
            'includeFlashcardAnswer' => $this->includeFlashcardAnswer,
            'includeNoteBody' => $this->includeNoteBody,
        ];
    }
}
