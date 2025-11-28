<?php

use App\Models\Discipline;
use App\Models\Notebook;
use App\Models\SimulatedExam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('computes accuracy and links to notebook and discipline', function (): void {
    $user = User::factory()->create();

    $notebook = new Notebook([
        'title' => 'Notebook',
        'description' => null,
    ]);
    $notebook->user()->associate($user);
    $notebook->save();

    $discipline = new Discipline([
        'title' => 'Math',
        'description' => null,
        'notebook_id' => $notebook->id,
    ]);
    $discipline->user()->associate($user);
    $discipline->save();

    $exam = new SimulatedExam([
        'scope_type' => 'notebook',
        'notebook_id' => $notebook->id,
        'discipline_id' => $discipline->id,
        'question_count' => 20,
        'answered_count' => 20,
        'correct_count' => 15,
        'incorrect_count' => 5,
        'score' => 75,
        'duration_seconds' => 600,
        'status' => 'completed',
        'questions' => [],
        'metadata' => ['source' => 'mock'],
        'started_at' => now()->subHour(),
        'completed_at' => now(),
    ]);
    $exam->user()->associate($user);
    $exam->save();

    expect($exam->accuracy)->toBe(75)
        ->and($exam->notebook->is($notebook))->toBeTrue()
        ->and($exam->discipline->is($discipline))->toBeTrue();
});

it('returns zero accuracy when there are no questions', function (): void {
    $user = User::factory()->create();

    $exam = new SimulatedExam([
        'scope_type' => 'general',
        'question_count' => 0,
        'answered_count' => 0,
        'correct_count' => 0,
        'incorrect_count' => 0,
        'score' => 0,
        'duration_seconds' => 0,
        'status' => 'draft',
        'questions' => [],
        'metadata' => [],
    ]);
    $exam->user()->associate($user);
    $exam->save();

    expect($exam->accuracy)->toBe(0);
});
