<?php

namespace App\Livewire\Notes;

use App\Models\Discipline;
use App\Models\Log;
use App\Models\Note;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CreateNote extends Component
{
    #[Locked]
    public Discipline $discipline;

    public string $title = '';

    public string $content = '';

    public bool $isFlashcard = false;

    public ?string $flashcardQuestion = null;

    public ?string $flashcardAnswer = null;

    public array $tags = [];

    public string $tagInput = '';

    public function mount(Discipline $discipline): void
    {
        $this->discipline = $discipline;
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
        $this->addTagsFromInput();

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
            'tags' => ['array'],
            'tags.*' => ['string', 'max:50'],
            'tagInput' => ['nullable', 'string', 'max:255'],
        ]);

        $note = Note::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_flashcard' => $validated['isFlashcard'],
            'flashcard_question' => $validated['isFlashcard'] ? $validated['flashcardQuestion'] : null,
            'flashcard_answer' => $validated['isFlashcard'] ? $validated['flashcardAnswer'] : null,
            'discipline_id' => $this->discipline->id,
        ]);

        $note->syncTags($validated['tags']);

        Log::create([
            'action' => 'note.created',
            'context' => [
                'note_id' => $note->id,
                'discipline_id' => $note->discipline_id,
                'is_flashcard' => $note->is_flashcard,
            ],
        ]);

        session()->flash('status', __('Note created successfully.'));

        $this->redirectRoute('notes.index', ['discipline' => $this->discipline->id], navigate: true);
    }

    public function addTagsFromInput(?string $value = null): void
    {
        $raw = $value ?? $this->tagInput;

        if (blank($raw)) {
            return;
        }

        $this->mergeTags($this->parseTags($raw));
        $this->tagInput = '';
    }

    public function removeTag(string $tag): void
    {
        $this->tags = collect($this->tags)
            ->reject(fn ($existing) => mb_strtolower($existing) === mb_strtolower($tag))
            ->values()
            ->all();
    }

    protected function mergeTags(array $candidates): void
    {
        if (empty($candidates)) {
            return;
        }

        $this->tags = collect([...$this->tags, ...$candidates])
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique(fn ($tag) => mb_strtolower($tag))
            ->values()
            ->all();
    }

    protected function parseTags(string $raw): array
    {
        return collect(preg_split('/[,\\n]/', $raw))
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.notes.create-note', [
            'discipline' => $this->discipline,
        ])->layout('layouts.app', [
            'title' => __('Create note'),
        ]);
    }
}
