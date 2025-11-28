<?php

use App\Models\FlashcardSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createSession(User $user, array $overrides = []): FlashcardSession
{
    $session = new FlashcardSession(array_merge([
        'status' => 'active',
        'total_cards' => 2,
        'current_index' => 0,
        'correct_count' => 0,
        'incorrect_count' => 0,
        'accuracy' => 0,
        'note_ids' => [10, 20],
        'studied_at' => now(),
        'discipline_id' => null,
    ], $overrides));

    $session->user()->associate($user);
    $session->save();

    return $session->fresh();
}

it('assesses pending cards and provides the current note', function (): void {
    $user = User::factory()->create();
    $session = createSession($user);

    expect($session->hasPendingCards())->toBeTrue()
        ->and($session->queueSize())->toBe(2)
        ->and($session->currentNoteId())->toBe(10);

    $session->update(['current_index' => 2]);

    expect($session->fresh()->hasPendingCards())->toBeFalse()
        ->and($session->currentNoteId())->toBeNull();
});

it('records correct answers and completes when queue finishes', function (): void {
    $user = User::factory()->create();
    $session = createSession($user, [
        'note_ids' => [42],
        'total_cards' => 1,
    ]);

    $session->recordAnswer(true);
    $session->refresh();

    expect($session->correct_count)->toBe(1)
        ->and($session->incorrect_count)->toBe(0)
        ->and($session->current_index)->toBe(1)
        ->and($session->status)->toBe('completed')
        ->and($session->accuracy)->toBe(100)
        ->and($session->completed_at)->not->toBeNull();
});

it('requeues incorrect answers and updates accuracy', function (): void {
    $user = User::factory()->create();
    $session = createSession($user, [
        'note_ids' => [7],
        'total_cards' => 1,
    ]);

    $session->recordAnswer(false);
    $session->refresh();

    expect($session->incorrect_count)->toBe(1)
        ->and($session->note_ids)->toBe([7, 7])
        ->and($session->status)->toBe('active')
        ->and($session->accuracy)->toBe(0);

    $session->recordAnswer(true);
    $session->refresh();

    expect($session->correct_count)->toBe(1)
        ->and($session->current_index)->toBe(2)
        ->and($session->status)->toBe('completed')
        ->and($session->accuracy)->toBe(50);
});

it('ensures completion status when no cards remain', function (): void {
    $user = User::factory()->create();
    $session = createSession($user, [
        'status' => 'active',
        'note_ids' => [1],
        'current_index' => 1,
        'completed_at' => null,
    ]);

    $result = $session->ensureStatusFromProgress();
    $session->refresh();

    expect($result)->toBeTrue()
        ->and($session->status)->toBe('completed')
        ->and($session->completed_at)->not->toBeNull();

    $session->update([
        'status' => 'active',
        'current_index' => 0,
    ]);

    expect($session->fresh()->ensureStatusFromProgress())->toBeFalse();
});

it('exposes accessor helpers for accuracy percentage and total reviewed', function (): void {
    $user = User::factory()->create();
    $session = createSession($user, [
        'correct_count' => 2,
        'incorrect_count' => 3,
    ]);

    $session->accuracy = null;

    expect($session->accuracy_percentage)->toBe(0)
        ->and($session->total_reviewed)->toBe(5);
});

it('ignores recordAnswer when the queue is empty', function (): void {
    $user = User::factory()->create();
    $session = createSession($user, [
        'note_ids' => [],
        'current_index' => 0,
    ]);

    $session->recordAnswer(true);

    $session->refresh();

    expect($session->correct_count)->toBe(0)
        ->and($session->status)->toBe('active')
        ->and($session->current_index)->toBe(0);
});
