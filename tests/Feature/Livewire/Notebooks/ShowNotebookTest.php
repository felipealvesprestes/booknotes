<?php

use App\Livewire\Notebooks\ShowNotebook;
use App\Models\Log;
use App\Models\Notebook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the notebook details', function (): void {
    $user = User::factory()->create();
    $notebook = Notebook::factory()->create(['title' => 'My Notebook', 'user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(ShowNotebook::class, ['notebook' => $notebook])
        ->assertViewHas('notebook', fn ($value) => $value->is($notebook))
        ->assertSet('notebook.title', 'My Notebook');
});

it('deletes a notebook, logs the action, and redirects', function (): void {
    $user = User::factory()->create();
    $notebook = Notebook::factory()->create(['title' => 'To Delete', 'user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(ShowNotebook::class, ['notebook' => $notebook])
        ->call('delete')
        ->assertRedirect(route('notebooks.index'));

    $this->assertDatabaseMissing('notebooks', ['id' => $notebook->id]);
    $this->assertDatabaseHas('logs', [
        'action' => 'notebook.deleted',
        'context->notebook_id' => $notebook->id,
    ]);
});
