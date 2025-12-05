<?php

use App\Jobs\GenerateNoteExportPdf;
use App\Livewire\Notes\ExportNotes;
use App\Models\Discipline;
use App\Models\Notebook;
use App\Models\Note;
use App\Models\NoteExport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Queue::fake();
});

it('validates scope selections and requires notes to export', function (): void {
    $user = User::factory()->create();
    $notebook = Notebook::factory()->create(['user_id' => $user->id]);
    $discipline = Discipline::factory()->create([
        'user_id' => $user->id,
        'notebook_id' => $notebook->id,
    ]);
    Note::factory()->create([
        'user_id' => $user->id,
        'discipline_id' => $discipline->id,
        'is_flashcard' => false,
    ]);

    Livewire::actingAs($user)
        ->test(ExportNotes::class)
        ->set('scope', 'notebook')
        ->call('export')
        ->assertHasErrors('export');

    Livewire::actingAs($user)
        ->test(ExportNotes::class)
        ->set('scope', 'notebook')
        ->set('selectedNotebook', $notebook->id)
        ->set('noteType', 'flashcards')
        ->call('export')
        ->assertHasErrors('export');
});

it('creates a note export and dispatches the generation job when notes exist', function (): void {
    $user = User::factory()->create();
    $notebook = Notebook::factory()->create(['user_id' => $user->id]);
    $discipline = Discipline::factory()->create([
        'user_id' => $user->id,
        'notebook_id' => $notebook->id,
    ]);

    Note::factory()->create([
        'user_id' => $user->id,
        'discipline_id' => $discipline->id,
        'is_flashcard' => true,
        'flashcard_question' => 'Explain inertia',
        'flashcard_answer' => 'Resistance to change in motion',
    ]);

    Livewire::actingAs($user)
        ->test(ExportNotes::class)
        ->set('scope', 'discipline')
        ->set('selectedDiscipline', $discipline->id)
        ->set('noteType', 'flashcards')
        ->set('includeNoteBody', false)
        ->call('export')
        ->assertHasNoErrors()
        ->assertSet('recentExportId', fn ($id) => NoteExport::whereKey($id)->exists());

    Queue::assertPushed(GenerateNoteExportPdf::class, 1);
    $export = NoteExport::latest()->first();
    expect($export)->not->toBeNull()
        ->and($export->filters['scope'])->toBe('discipline')
        ->and($export->filters['selectedDiscipline'])->toBe($discipline->id);
});
