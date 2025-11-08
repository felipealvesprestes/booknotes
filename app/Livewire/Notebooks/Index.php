<?php

namespace App\Livewire\Notebooks;

use App\Models\Log;
use App\Models\Notebook;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    /**
     * Atualiza a paginação quando o termo de busca muda.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Remove um caderno e registra a exclusão no log.
     */
    public function deleteNotebook(int $notebookId): void
    {
        $notebook = Notebook::findOrFail($notebookId);

        $context = [
            'notebook_id' => $notebook->id,
            'title' => $notebook->title,
        ];

        $notebook->delete();

        Log::create([
            'action' => 'notebook.deleted',
            'context' => $context,
        ]);

        session()->flash('status', __('Notebook deleted successfully.'));

        // Resetar para a primeira página se a atual ficar vazia.
        if ($this->getPage() > 1 && $this->pageIsEmpty()) {
            $this->previousPage();
        }
    }

    /**
     * Verifica se a página atual ficou vazia após a exclusão.
     */
    protected function pageIsEmpty(): bool
    {
        return $this->getNotebooksProperty()->count() === 0;
    }

    /**
     * Computed property com os notebooks paginados.
     */
    public function getNotebooksProperty()
    {
        return Notebook::query()
            ->latest()
            ->when($this->search, fn ($query) => $query->where('title', 'like', '%' . $this->search . '%'))
            ->paginate(10);
    }

    /**
     * Renderiza a view da página.
     */
    public function render(): View
    {
        return view('livewire.notebooks.index', [
            'notebooks' => $this->notebooks,
        ])->layout('layouts.app', [
            'title' => __('Notebooks'),
        ]);
    }
}
