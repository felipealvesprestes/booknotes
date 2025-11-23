<?php

namespace App\Livewire\Notes;

use App\Models\Discipline;
use App\Models\Log;
use App\Models\Note;
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

    protected function pageIsEmpty(): bool
    {
        return $this->getNotesProperty()->count() === 0;
    }

    public function getNotesProperty()
    {
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
            ->paginate($this->perPage);
    }

    public function render(): View
    {
        return view('livewire.notes.index', [
            'notes' => $this->notes,
            'discipline' => $this->discipline,
            'perPageOptions' => $this->perPageOptions,
        ])->layout('layouts.app', [
            'title' => __('Notes for :discipline', ['discipline' => $this->discipline->title]),
        ]);
    }
}
