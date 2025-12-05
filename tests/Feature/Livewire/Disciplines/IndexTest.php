<?php

use App\Livewire\Disciplines\Index;
use App\Models\Discipline;
use App\Models\Log;
use App\Models\Notebook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\Paginator;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('filters disciplines by search text and notebook filter', function (): void {
    $user = User::factory()->create();
    $notebookA = Notebook::factory()->create(['title' => 'Notebook A', 'user_id' => $user->id]);
    $notebookB = Notebook::factory()->create(['title' => 'Notebook B', 'user_id' => $user->id]);

    $physics = createDisciplineForIndex($user, $notebookA, ['title' => 'Physics Fundamentals']);
    $history = createDisciplineForIndex($user, $notebookB, ['title' => 'World History']);

    $component = Livewire::actingAs($user)->test(Index::class);

    $component->set('search', 'Physics')
        ->assertViewHas('disciplines', function ($paginator) use ($physics) {
            return $paginator->total() === 1 && $paginator->first()->is($physics);
        });

    $component->set('search', '')
        ->set('notebookFilter', $notebookB->id)
        ->assertViewHas('disciplines', function ($paginator) use ($history) {
            return $paginator->total() === 1 && $paginator->first()->is($history);
        });
});

it('deletes a discipline, logs the action, and flashes feedback', function (): void {
    $user = User::factory()->create();
    $notebook = Notebook::factory()->create(['user_id' => $user->id]);
    $discipline = createDisciplineForIndex($user, $notebook, ['title' => 'Chemistry']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('deleteDiscipline', $discipline->id);

    $this->assertDatabaseMissing('disciplines', ['id' => $discipline->id]);

    $log = Log::first();
    expect($log)->not->toBeNull()
        ->and($log->action)->toBe('discipline.deleted')
        ->and($log->context['discipline_id'] ?? null)->toBe($discipline->id);
});

it('resets pagination when search or notebook filters change', function (): void {
    $user = User::factory()->create();
    $notebooks = Notebook::factory()->count(2)->create(['user_id' => $user->id]);
    Discipline::factory()->count(15)->create([
        'user_id' => $user->id,
        'notebook_id' => $notebooks->first()->id,
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('gotoPage', 2)
        ->set('search', 'Physics')
        ->assertViewHas('disciplines', fn ($paginator) => $paginator->currentPage() === 1)
        ->set('search', '')
        ->call('gotoPage', 2)
        ->set('notebookFilter', $notebooks->last()->id)
        ->assertViewHas('disciplines', fn ($paginator) => $paginator->currentPage() === 1);
});

it('moves back a page when the last discipline on the page is deleted', function (): void {
    $user = User::factory()->create();
    $notebook = Notebook::factory()->create(['user_id' => $user->id]);
    $disciplines = Discipline::factory()->count(11)->create([
        'user_id' => $user->id,
        'notebook_id' => $notebook->id,
    ]);

    $lastDiscipline = $disciplines->last();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('gotoPage', 2)
        ->call('deleteDiscipline', $lastDiscipline->id)
        ->assertViewHas('disciplines', fn ($paginator) => $paginator->currentPage() === 1);
});

it('detects an empty page via the helper', function (): void {
    $user = User::factory()->create();
    $notebook = Notebook::factory()->create(['user_id' => $user->id]);
    Discipline::factory()->count(5)->create([
        'user_id' => $user->id,
        'notebook_id' => $notebook->id,
    ]);

    $this->actingAs($user);

    $component = new class extends Index
    {
        public function checkPageIsEmpty(): bool
        {
            return $this->pageIsEmpty();
        }
    };

    Paginator::currentPageResolver(fn () => 2);

    expect($component->checkPageIsEmpty())->toBeTrue();

    Paginator::currentPageResolver(fn () => null);
});

function createDisciplineForIndex(User $user, Notebook $notebook, array $attributes = []): Discipline
{
    $discipline = new Discipline(array_merge([
        'title' => 'Discipline',
        'description' => 'Description',
    ], $attributes));

    $discipline->user()->associate($user);
    $discipline->notebook()->associate($notebook);
    $discipline->save();

    return $discipline->fresh();
}
