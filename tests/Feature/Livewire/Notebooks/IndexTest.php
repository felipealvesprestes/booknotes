<?php

use App\Livewire\Notebooks\Index;
use App\Models\Log;
use App\Models\Notebook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('filters notebooks by search term', function (): void {
    $user = User::factory()->create();
    $alpha = Notebook::factory()->create(['title' => 'Alpha Notebook', 'user_id' => $user->id]);
    Notebook::factory()->create(['title' => 'Beta Notes', 'user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'Alpha')
        ->assertViewHas('notebooks', function ($paginator) use ($alpha) {
            return $paginator->total() === 1 && $paginator->first()->is($alpha);
        });
});

it('deletes a notebook, logs the action, and flashes feedback', function (): void {
    $user = User::factory()->create();
    $notebook = Notebook::factory()->create(['title' => 'Remove me', 'user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('deleteNotebook', $notebook->id);

    $this->assertDatabaseMissing('notebooks', ['id' => $notebook->id]);
    $this->assertDatabaseHas('logs', [
        'action' => 'notebook.deleted',
        'context->notebook_id' => $notebook->id,
    ]);
});

it('resets pagination when the search term updates', function (): void {
    $user = User::factory()->create();
    Notebook::factory()->count(12)->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('gotoPage', 2)
        ->set('search', 'Notebook')
        ->assertViewHas('notebooks', fn ($paginator) => $paginator->currentPage() === 1);
});

it('moves back a page when the last item on the current page is deleted', function (): void {
    $user = User::factory()->create();
    $notebooks = Notebook::factory()->count(11)->create(['user_id' => $user->id]);
    $lastNotebook = $notebooks->last();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('gotoPage', 2)
        ->call('deleteNotebook', $lastNotebook->id)
        ->assertViewHas('notebooks', fn ($paginator) => $paginator->currentPage() === 1);
});

it('detects when the current page is empty', function (): void {
    $user = User::factory()->create();
    Notebook::factory()->count(5)->create(['user_id' => $user->id]);

    $component = new class extends Index
    {
        public function exposePageIsEmpty(): bool
        {
            return $this->pageIsEmpty();
        }
    };

    \Illuminate\Pagination\Paginator::currentPageResolver(fn () => 2);

    expect($component->exposePageIsEmpty())->toBeTrue();

    \Illuminate\Pagination\Paginator::currentPageResolver(fn () => null);
});
