<?php

namespace App\Livewire\Notebooks;

use App\Models\Log;
use App\Models\Notebook;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class EditNotebook extends Component
{
    #[Locked]
    public Notebook $notebook;

    public string $title = '';

    public ?string $description = null;

    public function mount(Notebook $notebook): void
    {
        $this->title = $notebook->title;
        $this->description = $notebook->description;
    }

    /**
     * Atualiza o caderno e registra o log.
     */
    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $before = $this->notebook->only(['title', 'description']);

        $this->notebook->update($validated);

        Log::create([
            'action' => 'notebook.updated',
            'context' => [
                'notebook_id' => $this->notebook->id,
                'before' => $before,
                'after' => $this->notebook->only(['title', 'description']),
            ],
        ]);

        session()->flash('status', __('Notebook updated successfully.'));

        $this->redirectRoute('notebooks.index', navigate: true);

        return;
    }

    /**
     * Remove o caderno atual.
     */
    public function delete(): void
    {
        $context = [
            'notebook_id' => $this->notebook->id,
            'title' => $this->notebook->title,
        ];

        $this->notebook->delete();

        Log::create([
            'action' => 'notebook.deleted',
            'context' => $context,
        ]);

        session()->flash('status', __('Notebook deleted successfully.'));

        $this->redirectRoute('notebooks.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.notebooks.edit-notebook')
            ->layout('layouts.app', [
                'title' => __('Edit notebook'),
            ]);
    }
}
