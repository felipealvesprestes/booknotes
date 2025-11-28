<?php

use App\Livewire\Disciplines\CreateDiscipline;
use App\Livewire\Disciplines\EditDiscipline;
use App\Livewire\Disciplines\ShowDiscipline;
use App\Models\Discipline;
use App\Models\Log;
use App\Models\Notebook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('creates a discipline linked to a notebook and logs the action', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $notebook = Notebook::create([
        'title' => 'Notebook One',
        'description' => null,
    ]);

    Livewire::test(CreateDiscipline::class)
        ->set('title', 'Mathematics')
        ->set('description', 'Calculus notes')
        ->set('notebookId', $notebook->id)
        ->call('save')
        ->assertRedirect(route('disciplines.index'));

    $this->assertDatabaseHas('disciplines', [
        'title' => 'Mathematics',
        'notebook_id' => $notebook->id,
        'user_id' => $user->id,
    ]);

    $log = Log::first();
    expect($log?->action)->toBe('discipline.created');
    expect($log?->user_id)->toBe($user->id);
    expect($log?->context['notebook_id'])->toBe($notebook->id);
});

it('prefills notebook selection when query parameter is provided', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    Notebook::create([
        'title' => 'Notebook Default',
        'description' => null,
    ]);

    $targetNotebook = Notebook::create([
        'title' => 'Notebook Target',
        'description' => null,
    ]);

    Livewire::withQueryParams(['notebook' => $targetNotebook->id])
        ->test(CreateDiscipline::class)
        ->assertSet('notebookId', $targetNotebook->id);

    Livewire::withQueryParams([]);
});

it('ignores notebook query parameter that does not belong to the user', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::withQueryParams(['notebook' => 999])
        ->test(CreateDiscipline::class)
        ->assertSet('notebookId', null);

    Livewire::withQueryParams([]);
});

it('updates a discipline and stores the change in logs', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $notebook = Notebook::create([
        'title' => 'Notebook A',
        'description' => null,
    ]);

    $otherNotebook = Notebook::create([
        'title' => 'Notebook B',
        'description' => null,
    ]);

    $discipline = Discipline::create([
        'title' => 'History',
        'description' => 'World history',
        'notebook_id' => $notebook->id,
    ]);

    Livewire::test(EditDiscipline::class, ['discipline' => $discipline])
        ->set('title', 'Modern History')
        ->set('description', 'Modern era')
        ->set('notebookId', $otherNotebook->id)
        ->call('save')
        ->assertRedirect(route('disciplines.index'));

    $discipline->refresh();
    expect($discipline->title)->toBe('Modern History');
    expect($discipline->notebook_id)->toBe($otherNotebook->id);

    $log = Log::latest('id')->first();
    expect($log?->action)->toBe('discipline.updated');
    expect($log?->user_id)->toBe($user->id);
    expect($log?->context['before']['title'])->toBe('History');
    expect($log?->context['after']['notebook_id'])->toBe($otherNotebook->id);
});

it('deletes a discipline and logs the removal', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $notebook = Notebook::create([
        'title' => 'Notebook Z',
        'description' => null,
    ]);

    $discipline = Discipline::create([
        'title' => 'Physics',
        'description' => null,
        'notebook_id' => $notebook->id,
    ]);

    Livewire::test(EditDiscipline::class, ['discipline' => $discipline])
        ->call('delete')
        ->assertRedirect(route('disciplines.index'));

    $this->assertDatabaseMissing('disciplines', ['id' => $discipline->id]);

    $log = Log::latest('id')->first();
    expect($log?->action)->toBe('discipline.deleted');
    expect($log?->context)->toMatchArray([
        'discipline_id' => $discipline->id,
        'title' => 'Physics',
        'notebook_id' => $notebook->id,
    ]);
});

it('displays discipline details', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $notebook = Notebook::create([
        'title' => 'Notebook Display',
        'description' => null,
    ]);

    $discipline = Discipline::create([
        'title' => 'Discipline Display',
        'description' => 'About this discipline',
        'notebook_id' => $notebook->id,
    ]);

    Livewire::test(ShowDiscipline::class, ['discipline' => $discipline])
        ->assertSee('Discipline Display')
        ->assertSee($notebook->title)
        ->assertSee('About this discipline');
});
