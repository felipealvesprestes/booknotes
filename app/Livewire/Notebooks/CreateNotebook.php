<?php

namespace App\Livewire\Notebooks;

use App\Models\Log;
use App\Models\Notebook;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateNotebook extends Component
{
    public string $title = '';

    public ?string $description = null;

    /**
     * Valida e persiste o novo caderno.
     */
    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $notebook = Notebook::create($validated);

        Log::create([
            'action' => 'notebook.created',
            'context' => [
                'notebook_id' => $notebook->id,
                'title' => $notebook->title,
            ],
        ]);

        session()->flash('status', __('Notebook created successfully.'));

        $this->redirectRoute('notebooks.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.notebooks.create-notebook')
            ->layout('layouts.app', [
                'title' => __('Create notebook'),
            ]);
    }
}
