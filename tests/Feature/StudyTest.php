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
