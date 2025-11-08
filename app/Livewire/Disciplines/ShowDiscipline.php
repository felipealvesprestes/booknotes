<?php

namespace App\Livewire\Disciplines;

use App\Models\Discipline;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ShowDiscipline extends Component
{
    #[Locked]
    public Discipline $discipline;

    public function render(): View
    {
        return view('livewire.disciplines.show-discipline', [
            'discipline' => $this->discipline,
            'notesCount' => $this->discipline->notes()->count(),
        ])->layout('layouts.app', [
            'title' => $this->discipline->title,
        ]);
    }
}
