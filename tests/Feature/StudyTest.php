<?php

use App\Livewire\Study\Flashcards as StudyFlashcards;
use App\Models\Discipline;
use App\Models\FlashcardSession;
use App\Models\Notebook;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * Helper to create a discipline with a given number of flashcard notes.
 *
 * @return array{0: Discipline, 1: Collection<int, Note>}
 */
function seedDisciplineWithFlashcards(User $user, int $quantity = 2): array
{
    $notebook = Notebook::create([
        'title' => 'Notebook for study',
        'description' => null,
    ]);

    $discipline = Discipline::create([
        'title' => 'Discipline for study',
        'description' => null,
        'notebook_id' => $notebook->id,
    ]);

    $notes = Collection::times($quantity, function (int $index) use ($discipline) {
        return Note::create([
            'title' => "Flashcard {$index}",
            'content' => "Content {$index}",
            'is_flashcard' => true,
            'flashcard_question' => "Question {$index}",
            'flashcard_answer' => "Answer {$index}",
            'discipline_id' => $discipline->id,
        ]);
    });

    return [$discipline, $notes];
}

it('starts a study session and records progress', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    [$discipline, $notes] = seedDisciplineWithFlashcards($user, 2);

    $component = Livewire::test(StudyFlashcards::class)
        ->set('disciplineFilter', $discipline->id)
        ->call('startSession')
        ->assertHasNoErrors();

    $session = FlashcardSession::first();
    expect($session)->not->toBeNull();
    expect($session->total_cards)->toBe($notes->count());

    $component
        ->set('sessionId', $session->id)
        ->call('revealAnswer')
        ->call('markCorrect')
        ->call('revealAnswer')
        ->call('markIncorrect');

    $session->refresh();

    expect($session->correct_count)->toBe(1);
    expect($session->incorrect_count)->toBe(1);
    expect(count($session->note_ids))->toBeGreaterThan($session->total_cards);
});

it('prevents starting a session without flashcards', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test(StudyFlashcards::class)
        ->call('startSession')
        ->assertHasErrors(['session']);

    expect(FlashcardSession::count())->toBe(0);
});

it('completes a session and stores accuracy', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    [$discipline] = seedDisciplineWithFlashcards($user, 1);

    $component = Livewire::test(StudyFlashcards::class)
        ->set('disciplineFilter', $discipline->id)
        ->call('startSession')
        ->assertHasNoErrors();

    $session = FlashcardSession::first();
    expect($session)->not->toBeNull();

    $component
        ->set('sessionId', $session->id)
        ->call('revealAnswer')
        ->call('markCorrect');

    $session->refresh();

    expect($session->status)->toBe('completed');
    expect($session->accuracy)->toBe(100);
    expect($session->correct_count)->toBe(1);
    expect($session->incorrect_count)->toBe(0);
    expect($session->completed_at)->not->toBeNull();
});

it('resumes longstanding active sessions instead of creating new ones', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    [$discipline, $notes] = seedDisciplineWithFlashcards($user, 2);

    $session = FlashcardSession::create([
        'status' => 'active',
        'total_cards' => $notes->count(),
        'current_index' => 0,
        'correct_count' => 0,
        'incorrect_count' => 0,
        'accuracy' => 0,
        'note_ids' => $notes->pluck('id')->all(),
        'studied_at' => now()->subDays(7),
        'discipline_id' => $discipline->id,
    ]);

    Livewire::test(StudyFlashcards::class)
        ->set('disciplineFilter', $discipline->id)
        ->call('startSession')
        ->assertSet('sessionId', $session->id);

    expect(FlashcardSession::count())->toBe(1);
});

it('loads sessions directly when visiting with the session query parameter', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    [$discipline, $notes] = seedDisciplineWithFlashcards($user, 2);

    $session = FlashcardSession::create([
        'status' => 'active',
        'total_cards' => $notes->count(),
        'current_index' => 0,
        'correct_count' => 0,
        'incorrect_count' => 0,
        'accuracy' => 0,
        'note_ids' => $notes->pluck('id')->all(),
        'studied_at' => now()->subDay(),
        'discipline_id' => $discipline->id,
    ]);

    Livewire::withQueryParams(['session' => $session->id])
        ->test(StudyFlashcards::class)
        ->assertSet('sessionId', $session->id)
        ->assertSet('disciplineFilter', $discipline->id);

    Livewire::withQueryParams([]);
});

it('marks finished sessions as completed when loading from the query string', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    [$discipline, $notes] = seedDisciplineWithFlashcards($user, 2);

    $session = FlashcardSession::create([
        'status' => 'active',
        'total_cards' => $notes->count(),
        'current_index' => $notes->count(),
        'correct_count' => $notes->count(),
        'incorrect_count' => 0,
        'accuracy' => 100,
        'note_ids' => $notes->pluck('id')->all(),
        'studied_at' => now()->subWeek(),
        'discipline_id' => $discipline->id,
    ]);

    Livewire::withQueryParams(['session' => $session->id])
        ->test(StudyFlashcards::class)
        ->assertSet('sessionId', $session->id);

    expect($session->fresh()->status)->toBe('completed');

    Livewire::withQueryParams([]);
});

it('ignores stale sessions without pending cards when starting a new one', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    [$discipline, $notes] = seedDisciplineWithFlashcards($user, 2);

    $staleSession = FlashcardSession::create([
        'status' => 'active',
        'total_cards' => $notes->count(),
        'current_index' => $notes->count(),
        'correct_count' => $notes->count(),
        'incorrect_count' => 0,
        'accuracy' => 100,
        'note_ids' => $notes->pluck('id')->all(),
        'studied_at' => now()->subDays(3),
        'discipline_id' => $discipline->id,
    ]);

    Livewire::test(StudyFlashcards::class)
        ->set('disciplineFilter', $discipline->id)
        ->call('startSession')
        ->assertHasNoErrors();

    $staleSession->refresh();

    expect($staleSession->status)->toBe('completed');
    expect(FlashcardSession::query()->where('status', 'active')->count())->toBe(1);
});

it('skips missing flashcards when resuming a session', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    [$discipline, $notes] = seedDisciplineWithFlashcards($user, 3);

    Livewire::test(StudyFlashcards::class)
        ->set('disciplineFilter', $discipline->id)
        ->call('startSession')
        ->assertHasNoErrors();

    $session = FlashcardSession::first();
    $firstNoteId = $session->note_ids[0];

    Note::find($firstNoteId)?->delete();

    $component = Livewire::test(StudyFlashcards::class)
        ->assertSet('sessionId', $session->id);

    $currentCard = $component->viewData('currentCard');

    expect($currentCard)->not->toBeNull();
    expect($currentCard->id)->not->toBe($firstNoteId);
    expect($session->fresh()->note_ids)->not->toContain($firstNoteId);
});
