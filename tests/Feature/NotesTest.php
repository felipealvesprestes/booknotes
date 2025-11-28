<?php

use App\Livewire\Notes\CreateNote;
use App\Livewire\Notes\EditNote;
use App\Livewire\Notes\Index as NotesIndex;
use App\Livewire\Notes\Library as NotesLibrary;
use App\Livewire\Notes\ShowNote;
use App\Models\Discipline;
use App\Models\Log;
use App\Models\Notebook;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function createDiscipline(User $user): Discipline {
    $notebook = Notebook::create([
        'title' => 'Notebook Seed',
        'description' => null,
    ]);

    return Discipline::create([
        'title' => 'Discipline Seed',
        'description' => null,
        'notebook_id' => $notebook->id,
    ]);
}

it('creates a note and logs the action', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $discipline = createDiscipline($user);

    Livewire::test(CreateNote::class, ['discipline' => $discipline])
        ->set('title', 'First Note')
        ->set('content', 'This is the note body.')
        ->set('isFlashcard', true)
        ->set('flashcardQuestion', 'What is first?')
        ->set('flashcardAnswer', 'Answer')
        ->call('save')
        ->assertRedirect(route('notes.create', $discipline));

    $this->assertDatabaseHas('notes', [
        'title' => 'First Note',
        'discipline_id' => $discipline->id,
        'user_id' => $user->id,
        'is_flashcard' => true,
    ]);

    $log = Log::first();
    expect($log?->action)->toBe('note.created');
    expect($log?->user_id)->toBe($user->id);
});

it('updates a note and records the changes', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $discipline = createDiscipline($user);

    $note = Note::create([
        'title' => 'Original',
        'content' => 'Original content',
        'is_flashcard' => false,
        'discipline_id' => $discipline->id,
    ]);

    Livewire::test(EditNote::class, ['discipline' => $discipline, 'note' => $note])
        ->set('title', 'Updated title')
        ->set('content', 'Updated content')
        ->set('isFlashcard', true)
        ->set('flashcardQuestion', 'Updated q')
        ->set('flashcardAnswer', 'Updated answer')
        ->call('save')
        ->assertRedirect(route('notes.index', $discipline));

    $note->refresh();
    expect($note->title)->toBe('Updated title');
    expect($note->is_flashcard)->toBeTrue();

    $log = Log::latest('id')->first();
    expect($log?->action)->toBe('note.updated');
    expect($log?->context['after']['is_flashcard'])->toBeTrue();
});

it('converts and reverts flashcards from the list', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $discipline = createDiscipline($user);

    $note = Note::create([
        'title' => 'Convertible',
        'content' => 'Body',
        'is_flashcard' => false,
        'discipline_id' => $discipline->id,
    ]);

    Livewire::test(NotesIndex::class, ['discipline' => $discipline])
        ->call('convertToFlashcard', $note->id);

    $note->refresh();
    expect($note->is_flashcard)->toBeTrue();

    Livewire::test(NotesIndex::class, ['discipline' => $discipline])
        ->call('revertFlashcard', $note->id);

    $note->refresh();
    expect($note->is_flashcard)->toBeFalse();
});

it('shows a note and toggles flashcard state from the show page', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $discipline = createDiscipline($user);

    $note = Note::create([
        'title' => 'Showcase',
        'content' => 'Body content',
        'is_flashcard' => false,
        'discipline_id' => $discipline->id,
    ]);

    Livewire::test(ShowNote::class, ['discipline' => $discipline, 'note' => $note])
        ->call('convertToFlashcard')
        ->assertHasNoErrors();

    $note->refresh();
    expect($note->is_flashcard)->toBeTrue();

    Livewire::test(ShowNote::class, ['discipline' => $discipline, 'note' => $note])
        ->call('revertFlashcard')
        ->assertHasNoErrors();

    $note->refresh();
    expect($note->is_flashcard)->toBeFalse();
});

it('lists notes in the library with filters', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $disciplineA = createDiscipline($user);
    $disciplineB = Discipline::create([
        'title' => 'Discipline B',
        'description' => null,
        'notebook_id' => $disciplineA->notebook_id,
    ]);

    $noteA = Note::create([
        'title' => 'Alpha',
        'content' => 'Content A',
        'discipline_id' => $disciplineA->id,
        'is_flashcard' => false,
    ]);

    $noteB = Note::create([
        'title' => 'Beta',
        'content' => 'Content B',
        'discipline_id' => $disciplineB->id,
        'is_flashcard' => true,
        'flashcard_question' => 'Q',
        'flashcard_answer' => 'A',
    ]);

    Livewire::test(NotesLibrary::class)
        ->assertSee('Alpha')
        ->assertSee('Beta')
        ->set('disciplineFilter', (string) $disciplineA->id)
        ->assertSee('Alpha')
        ->assertDontSee('Beta')
        ->set('disciplineFilter', (string) $disciplineB->id)
        ->set('flashcardFilter', 'flashcards')
        ->assertSee('Beta');
});

it('deletes a note and logs the deletion', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $discipline = createDiscipline($user);

    $note = Note::create([
        'title' => 'To delete',
        'content' => 'Content',
        'is_flashcard' => false,
        'discipline_id' => $discipline->id,
    ]);

    Livewire::test(EditNote::class, ['discipline' => $discipline, 'note' => $note])
        ->call('delete')
        ->assertRedirect(route('notes.index', $discipline));

    $this->assertDatabaseMissing('notes', ['id' => $note->id]);

    $log = Log::latest('id')->first();
    expect($log?->action)->toBe('note.deleted');
});
