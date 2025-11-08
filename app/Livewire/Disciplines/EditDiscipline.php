<?php

namespace App\Livewire\Disciplines;

use App\Models\Discipline;
use App\Models\Log;
use App\Models\Notebook;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class EditDiscipline extends Component
{
    #[Locked]
    public Discipline $discipline;

    public string $title = '';

    public ?string $description = null;

    public $notebookId = null;

    public function mount(Discipline $discipline): void
    {
        $this->title = $discipline->title;
        $this->description = $discipline->description;
        $this->notebookId = $discipline->notebook_id;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'notebookId' => [
                'required',
                Rule::exists('notebooks', 'id')->where('user_id', auth()->id()),
            ],
        ], [], [
            'notebookId' => __('notebook'),
        ]);

        $before = $this->discipline->only(['title', 'description', 'notebook_id']);

        $this->discipline->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'notebook_id' => $validated['notebookId'],
        ]);

        Log::create([
            'action' => 'discipline.updated',
            'context' => [
                'discipline_id' => $this->discipline->id,
                'before' => $before,
                'after' => $this->discipline->only(['title', 'description', 'notebook_id']),
            ],
        ]);

        session()->flash('status', __('Discipline updated successfully.'));

        $this->redirectRoute('disciplines.index', navigate: true);
    }

    public function delete(): void
    {
        $context = [
            'discipline_id' => $this->discipline->id,
            'title' => $this->discipline->title,
            'notebook_id' => $this->discipline->notebook_id,
        ];

        $this->discipline->delete();

        Log::create([
            'action' => 'discipline.deleted',
            'context' => $context,
        ]);

        session()->flash('status', __('Discipline deleted successfully.'));

        $this->redirectRoute('disciplines.index', navigate: true);
    }

    public function render(): View
    {
        $notebooks = auth()->user()->notebooks()->select('id', 'title')->orderBy('title')->get();

        return view('livewire.disciplines.edit-discipline', compact('notebooks'))
            ->layout('layouts.app', [
                'title' => __('Edit discipline'),
            ]);
    }
}
