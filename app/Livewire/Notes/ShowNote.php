<?php

namespace App\Livewire\Notes;

use App\Models\Discipline;
use App\Models\Log;
use App\Models\Note;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ShowNote extends Component
{
    #[Locked]
    public Discipline $discipline;

    #[Locked]
    public Note $note;

    public function mount(Discipline $discipline, Note $note): void
    {
        abort_if($note->discipline_id !== $discipline->id, 404);

        $this->discipline = $discipline;
        $this->note = $note->load('tags');
    }

    public function convertToFlashcard(): void
    {
        if ($this->note->is_flashcard) {
            return;
        }

        $this->note->update([
            'is_flashcard' => true,
            'flashcard_question' => $this->note->flashcard_question ?: $this->note->title,
            'flashcard_answer' => $this->note->flashcard_answer ?: $this->note->content,
        ]);

        Log::create([
            'action' => 'note.converted_to_flashcard',
            'context' => [
                'note_id' => $this->note->id,
                'discipline_id' => $this->note->discipline_id,
            ],
        ]);

        session()->flash('status', __('Note converted to flashcard.'));

        $this->refreshNote();
    }

    public function revertFlashcard(): void
    {
        if (! $this->note->is_flashcard) {
            return;
        }

        $this->note->update([
            'is_flashcard' => false,
        ]);

        Log::create([
            'action' => 'note.reverted_from_flashcard',
            'context' => [
                'note_id' => $this->note->id,
                'discipline_id' => $this->note->discipline_id,
            ],
        ]);

        session()->flash('status', __('Note marked as regular note.'));

        $this->refreshNote();
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

    protected function refreshNote(): void
    {
        $this->note->refresh()->load('tags');
    }

    public function render(): View
    {
        return view('livewire.notes.show-note', [
            'discipline' => $this->discipline,
            'note' => $this->note,
        ])->layout('layouts.app', [
            'title' => $this->note->title,
        ]);
    }
}
