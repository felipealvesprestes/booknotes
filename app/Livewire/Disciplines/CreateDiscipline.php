<?php

namespace App\Livewire\Disciplines;

use App\Models\Discipline;
use App\Models\Log;
use App\Models\Notebook;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreateDiscipline extends Component
{
    public string $title = '';

    public ?string $description = null;

    public $notebookId = null;

    public function mount(): void
    {
        $preferredNotebookId = request()->integer('notebook');

        if (! $preferredNotebookId) {
            return;
        }

        $hasNotebook = Notebook::query()
            ->where('user_id', auth()->id())
            ->whereKey($preferredNotebookId)
            ->exists();

        if ($hasNotebook) {
            $this->notebookId = $preferredNotebookId;
        }
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

        $discipline = Discipline::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'notebook_id' => $validated['notebookId'],
        ]);

        Log::create([
            'action' => 'discipline.created',
            'context' => [
                'discipline_id' => $discipline->id,
                'title' => $discipline->title,
                'notebook_id' => $discipline->notebook_id,
            ],
        ]);

        session()->flash('status', __('Discipline created successfully.'));

        $this->redirectRoute('disciplines.index', navigate: true);
    }

    public function render(): View
    {
        $notebooks = auth()->user()->notebooks()->select('id', 'title')->orderBy('title')->get();

        return view('livewire.disciplines.create-discipline', compact('notebooks'))
            ->layout('layouts.app', [
                'title' => __('Create discipline'),
            ]);
    }
}
