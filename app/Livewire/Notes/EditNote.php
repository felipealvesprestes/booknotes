<?php

namespace App\Livewire\Notes;

use App\Models\Discipline;
use App\Models\Log;
use App\Models\Note;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class EditNote extends Component
{
    #[Locked]
    public Discipline $discipline;

    #[Locked]
    public Note $note;

    public string $title = '';

    public string $content = '';

    public bool $isFlashcard = false;

    public ?string $flashcardQuestion = null;

    public ?string $flashcardAnswer = null;

    public function mount(Discipline $discipline, Note $note): void
    {
        abort_if($note->discipline_id !== $discipline->id, 404);

        $this->discipline = $discipline;
        $this->note = $note;

        $this->title = $note->title;
        $this->content = $note->content;
        $this->isFlashcard = $note->is_flashcard;
        $this->flashcardQuestion = $note->flashcard_question;
        $this->flashcardAnswer = $note->flashcard_answer;
    }

    public function updatedIsFlashcard(bool $value): void
    {
        if ($value) {
            if (blank($this->flashcardQuestion)) {
                $this->flashcardQuestion = $this->title;
            }

            if (blank($this->flashcardAnswer)) {
                $this->flashcardAnswer = $this->content;
            }

            return;
        }

        $this->flashcardQuestion = null;
        $this->flashcardAnswer = null;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'isFlashcard' => ['boolean'],
            'flashcardQuestion' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf($this->isFlashcard),
            ],
            'flashcardAnswer' => [
                'nullable',
                'string',
                Rule::requiredIf($this->isFlashcard),
            ],
        ]);

        $before = $this->note->only([
            'title',
            'content',
            'is_flashcard',
            'flashcard_question',
            'flashcard_answer',
        ]);

        $this->note->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_flashcard' => $validated['isFlashcard'],
            'flashcard_question' => $validated['isFlashcard'] ? $validated['flashcardQuestion'] : null,
            'flashcard_answer' => $validated['isFlashcard'] ? $validated['flashcardAnswer'] : null,
        ]);

        Log::create([
            'action' => 'note.updated',
            'context' => [
                'note_id' => $this->note->id,
                'discipline_id' => $this->note->discipline_id,
                'before' => $before,
                'after' => $this->note->only([
                    'title',
                    'content',
                    'is_flashcard',
                    'flashcard_question',
                    'flashcard_answer',
                ]),
            ],
        ]);

        session()->flash('status', __('Note updated successfully.'));

        $this->redirectRoute('notes.index', ['discipline' => $this->discipline->id], navigate: true);
    }

    public function delete(): void
    {
        $context = [
            'note_id' => $this->note->id,
            'title' => $this->note->title,
            'discipline_id' => $this->discipline->id,
        ];

        $this->note->delete();

        Log::create([
            'action' => 'note.deleted',
            'context' => $context,
        ]);

        session()->flash('status', __('Note deleted successfully.'));

        $this->redirectRoute('notes.index', ['discipline' => $this->discipline->id], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.notes.edit-note', [
            'discipline' => $this->discipline,
        ])->layout('layouts.app', [
            'title' => __('Edit note'),
        ]);
    }
}
