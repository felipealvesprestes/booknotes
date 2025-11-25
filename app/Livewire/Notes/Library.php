<?php

namespace App\Livewire\Notes;

use App\Models\Discipline;
use App\Models\Log;
use App\Models\Note;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Library extends Component
{
    use WithPagination;

    public string $search = '';

    public string $flashcardFilter = 'all';

    public $disciplineFilter = null;

    public int $perPage = 10;

    public array $selectedTags = [];

    protected array $perPageOptions = [10, 30, 50];

    protected $queryString = [
        'search' => ['except' => ''],
        'flashcardFilter' => ['except' => 'all'],
        'disciplineFilter' => ['except' => null],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFlashcardFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDisciplineFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDisciplineFilter($value): void
    {
        $this->disciplineFilter = $value ?: null;
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

    public function deleteNote(int $noteId): void
    {
        $note = Note::findOrFail($noteId);

        $context = [
            'note_id' => $note->id,
            'title' => $note->title,
            'discipline_id' => $note->discipline_id,
        ];

        $note->delete();

        Log::create([
            'action' => 'note.deleted',
            'context' => $context,
        ]);

        session()->flash('status', __('Note deleted successfully.'));

        if ($this->getPage() > 1 && $this->getNotesProperty()->count() === 0) {
            $this->previousPage();
        }
    }

    public function convertToFlashcard(int $noteId): void
    {
        $note = Note::findOrFail($noteId);

        if ($note->is_flashcard) {
            return;
        }

        $note->update([
            'is_flashcard' => true,
            'flashcard_question' => $note->flashcard_question ?: $note->title,
            'flashcard_answer' => $note->flashcard_answer ?: $note->content,
        ]);

        Log::create([
            'action' => 'note.converted_to_flashcard',
            'context' => [
                'note_id' => $note->id,
                'discipline_id' => $note->discipline_id,
            ],
        ]);

        session()->flash('status', __('Note converted to flashcard.'));
    }

    public function revertFlashcard(int $noteId): void
    {
        $note = Note::findOrFail($noteId);

        if (! $note->is_flashcard) {
            return;
        }

        $note->update([
            'is_flashcard' => false,
        ]);

        Log::create([
            'action' => 'note.reverted_from_flashcard',
            'context' => [
                'note_id' => $note->id,
                'discipline_id' => $note->discipline_id,
            ],
        ]);

        session()->flash('status', __('Note marked as regular note.'));
    }

    public function toggleTagFilter(int $tagId): void
    {
        $tagId = (int) $tagId;

        if ($tagId <= 0) {
            return;
        }

        $current = $this->sanitizedTagFilters();

        if (in_array($tagId, $current, true)) {
            $current = array_values(array_diff($current, [$tagId]));
        } else {
            $current[] = $tagId;
        }

        $this->selectedTags = $current;

        $this->resetPage();
    }

    public function clearTagFilter(): void
    {
        if (empty($this->selectedTags)) {
            return;
        }

        $this->selectedTags = [];
        $this->resetPage();
    }

    protected function sanitizedTagFilters(): array
    {
        return collect($this->selectedTags ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function getNotesProperty()
    {
        $tagIds = $this->sanitizedTagFilters();

        return Note::query()
            ->with(['discipline', 'tags'])
            ->latest()
            ->when($this->search, fn ($query) => $query->where('title', 'like', '%' . $this->search . '%'))
            ->when($this->flashcardFilter !== 'all', function ($query) {
                if ($this->flashcardFilter === 'flashcards') {
                    $query->where('is_flashcard', true);
                } elseif ($this->flashcardFilter === 'notes') {
                    $query->where('is_flashcard', false);
                }
            })
            ->when($this->disciplineFilter, fn ($query) => $query->where('discipline_id', $this->disciplineFilter))
            ->when(! empty($tagIds), fn ($query) => $query->whereHas(
                'tags',
                fn ($tagQuery) => $tagQuery->whereIn('tags.id', $tagIds)
            ))
            ->paginate($this->perPage);
    }

    public function render(): View
    {
        $disciplines = Discipline::query()
            ->orderBy('title')
            ->select('id', 'title')
            ->get();

        $availableTags = Tag::query()
            ->select('id', 'name')
            ->whereHas('notes')
            ->orderBy('name')
            ->get();

        return view('livewire.notes.library', [
            'notes' => $this->notes,
            'disciplines' => $disciplines,
            'perPageOptions' => $this->perPageOptions,
            'availableTags' => $availableTags,
        ])->layout('layouts.app', [
            'title' => __('All notes'),
        ]);
    }
}
