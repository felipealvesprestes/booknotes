<?php

use App\Livewire\Notebooks\CreateNotebook;
use App\Livewire\Notebooks\EditNotebook;
use App\Models\Log;
use App\Models\Notebook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('allows a user to create a notebook and logs the action', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(CreateNotebook::class)
        ->set('title', 'My First Notebook')
        ->set('description', 'Notebook description')
        ->call('save')
        ->assertRedirect(route('notebooks.index'));

    $this->assertDatabaseHas('notebooks', [
        'title' => 'My First Notebook',
        'user_id' => $user->id,
    ]);

    $log = Log::first();

    expect($log)->not->toBeNull();
    expect($log->action)->toBe('notebook.created');
    expect($log->user_id)->toBe($user->id);
    expect($log->context)->toMatchArray([
        'title' => 'My First Notebook',
        'notebook_id' => Notebook::first()->id,
    ]);
});

it('logs updates to a notebook', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    $notebook = Notebook::create([
        'title' => 'Original Title',
        'description' => 'Original description',
    ]);

    Livewire::test(EditNotebook::class, ['notebook' => $notebook])
        ->set('title', 'Updated Title')
        ->set('description', 'Updated description')
        ->call('save');

    $notebook->refresh();

    expect($notebook->title)->toBe('Updated Title');
    expect($notebook->description)->toBe('Updated description');

    $log = Log::latest('id')->first();

    expect($log->action)->toBe('notebook.updated');
    expect($log->user_id)->toBe($user->id);
    expect($log->context['before']['title'])->toBe('Original Title');
    expect($log->context['after']['title'])->toBe('Updated Title');
});

it('logs deletions of a notebook', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    $notebook = Notebook::create([
        'title' => 'Notebook to delete',
        'description' => null,
    ]);

    Livewire::test(EditNotebook::class, ['notebook' => $notebook])
        ->call('delete')
        ->assertRedirect(route('notebooks.index'));

    $this->assertDatabaseMissing('notebooks', ['id' => $notebook->id]);

    $log = Log::latest('id')->first();

    expect($log->action)->toBe('notebook.deleted');
    expect($log->user_id)->toBe($user->id);
    expect($log->context)->toMatchArray([
        'notebook_id' => $notebook->id,
        'title' => 'Notebook to delete',
    ]);
});
