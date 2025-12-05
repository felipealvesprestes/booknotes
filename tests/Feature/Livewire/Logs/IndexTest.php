<?php

use App\Livewire\Logs\Index;
use App\Models\Discipline;
use App\Models\Log;
use App\Models\Notebook;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('filters logs by action, search, and per-page settings', function (): void {
    $user = User::factory()->create();

    createLog($user, 'note.created', ['title' => 'Biology note']);
    createLog($user, 'note.deleted', ['title' => 'History note']);

    $component = Livewire::actingAs($user)->test(Index::class);

    $component->set('actionFilter', 'note.created')
        ->assertViewHas('logs', function ($logs) {
            return $logs->total() === 1 && $logs->first()['action'] === 'note.created';
        });

    $component->set('actionFilter', null)
        ->set('search', 'History')
        ->assertViewHas('logs', function ($logs) {
            return $logs->total() === 1 && $logs->first()['description'] !== null;
        });

    $component->set('search', '')
        ->set('perPage', 30)
        ->assertSet('perPage', 30);
});

it('transforms log entries with meta tags and descriptions', function (): void {
    $user = User::factory()->create();
    $notebook = Notebook::factory()->create(['user_id' => $user->id, 'title' => 'Science']);
    $discipline = Discipline::factory()->create(['notebook_id' => $notebook->id, 'user_id' => $user->id, 'title' => 'Physics']);
    $note = Note::factory()->create([
        'discipline_id' => $discipline->id,
        'user_id' => $user->id,
        'title' => 'Kinematics',
    ]);

    createLog($user, 'note.converted_to_flashcard', [
        'note_id' => $note->id,
        'discipline_id' => $discipline->id,
        'notebook_id' => $notebook->id,
        'is_flashcard' => true,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->assertViewHas('logs', function ($logs) {
            $entry = $logs->first();

            return $entry['label'] === __('Note converted to flashcard')
                && collect($entry['meta'])->contains(fn ($meta) => $meta['label'] === __('Discipline'))
                && collect($entry['tags'])->contains(fn ($tag) => $tag['text'] === __('Flashcard'));
        });
});

function createLog(User $user, string $action, array $context = []): void
{
    $log = new Log([
        'action' => $action,
        'context' => $context,
    ]);

    $log->user()->associate($user);
    $log->created_at = Carbon::now();
    $log->updated_at = Carbon::now();
    $log->save();
}
