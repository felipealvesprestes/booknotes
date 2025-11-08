<?php

namespace App\Livewire\Notebooks;

use App\Models\Notebook;
use App\Models\Log;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ShowNotebook extends Component
{
    #[Locked]
    public Notebook $notebook;

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
        return view('livewire.notebooks.show-notebook', [
            'notebook' => $this->notebook,
        ])->layout('layouts.app', [
            'title' => $this->notebook->title,
        ]);
    }
}
