<?php

namespace App\Livewire\Notes;

use App\Models\Discipline;
use App\Models\Log;
use App\Models\Note;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Locked]
    public Discipline $discipline;

    public string $search = '';

    public string $flashcardFilter = 'all';

    public int $perPage = 10;

    public array $selectedTags = [];

    protected array $perPageOptions = [10, 30, 50];

    protected $queryString = [
        'search' => ['except' => ''],
        'flashcardFilter' => ['except' => 'all'],
        'perPage' => ['except' => 10],
    ];

    public function mount(Discipline $discipline): void
    {
        $this->discipline = $discipline;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFlashcardFilter(): void
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

    public function deleteNote(int $noteId): void
    {
        $note = $this->discipline->notes()->findOrFail($noteId);

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

        if ($this->getPage() > 1 && $this->pageIsEmpty()) {
            $this->previousPage();
        }
    }

    public function convertToFlashcard(int $noteId): void
    {
        $note = $this->discipline->notes()->findOrFail($noteId);

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
        $note = $this->discipline->notes()->findOrFail($noteId);

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

    protected function pageIsEmpty(): bool
    {
        return $this->getNotesProperty()->count() === 0;
    }

    public function getNotesProperty()
    {
        $tagIds = $this->sanitizedTagFilters();

        return Note::query()
            ->with(['discipline', 'tags'])
            ->whereBelongsTo($this->discipline)
            ->latest()
            ->when($this->search, fn ($query) => $query->where('title', 'like', '%' . $this->search . '%'))
            ->when($this->flashcardFilter !== 'all', function ($query) {
                if ($this->flashcardFilter === 'flashcards') {
                    $query->where('is_flashcard', true);
                } elseif ($this->flashcardFilter === 'notes') {
                    $query->where('is_flashcard', false);
                }
            })
            ->when(! empty($tagIds), fn ($query) => $query->whereHas(
                'tags',
                fn ($tagQuery) => $tagQuery->whereIn('tags.id', $tagIds)
            ))
            ->paginate($this->perPage);
    }

    public function render(): View
    {
        $availableTags = Tag::query()
            ->select('tags.id', 'tags.name')
            ->whereHas('notes', fn ($query) => $query->where('discipline_id', $this->discipline->id))
            ->orderBy('name')
            ->get();

        return view('livewire.notes.index', [
            'notes' => $this->notes,
            'discipline' => $this->discipline,
            'perPageOptions' => $this->perPageOptions,
            'availableTags' => $availableTags,
        ])->layout('layouts.app', [
            'title' => __('Notes for :discipline', ['discipline' => $this->discipline->title]),
        ]);
    }
}
