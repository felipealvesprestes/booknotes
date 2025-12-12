<?php

use App\Livewire\Dashboard\Overview;
use App\Models\FlashcardSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('does not list archived flashcard sessions as recent queues', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $archived = FlashcardSession::create([
        'status' => 'archived',
        'total_cards' => 1,
        'current_index' => 0,
        'correct_count' => 0,
        'incorrect_count' => 0,
        'accuracy' => 0,
        'note_ids' => [1],
        'studied_at' => now()->subHour(),
    ]);

    $active = FlashcardSession::create([
        'status' => 'active',
        'total_cards' => 2,
        'current_index' => 0,
        'correct_count' => 0,
        'incorrect_count' => 0,
        'accuracy' => 0,
        'note_ids' => [1, 2],
        'studied_at' => now(),
    ]);

    $recentSessions = Livewire::test(Overview::class)
        ->viewData('recentSessions');

    $recentIds = collect($recentSessions)->pluck('id');

    expect($recentIds)->toContain($active->id);
    expect($recentIds)->not->toContain($archived->id);
});
