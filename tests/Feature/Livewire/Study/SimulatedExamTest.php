<?php

use App\Livewire\Study\SimulatedExam;
use App\Models\Discipline;
use App\Models\Notebook;
use App\Models\Note;
use App\Models\SimulatedExam as SimulatedExamModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow(Carbon::parse('2024-01-01 10:00:00'));
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('prevents starting when cards are missing and succeeds once requirements are met', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $notebook = Notebook::factory()->create(['user_id' => $user->id]);
    $discipline = Discipline::factory()->create([
        'notebook_id' => $notebook->id,
        'user_id' => $user->id,
    ]);

    Livewire::test(SimulatedExam::class)
        ->set('scopeType', 'discipline')
        ->set('disciplineId', $discipline->id)
        ->set('questionCount', 10)
        ->call('startExam')
        ->assertHasErrors(['exam' => __('Add flashcards to this selection to unlock the simulated test.')]);

    createFlashcards($discipline, 8, uniqueAnswers: true);

    Livewire::test(SimulatedExam::class)
        ->set('scopeType', 'discipline')
        ->set('disciplineId', $discipline->id)
        ->set('questionCount', 10)
        ->call('startExam')
        ->assertHasErrors(['exam' => __('You need at least :count flashcards for this selection.', ['count' => 10])]);

    createFlashcards($discipline, 2, uniqueAnswers: true);

    Livewire::test(SimulatedExam::class)
        ->set('scopeType', 'discipline')
        ->set('disciplineId', $discipline->id)
        ->set('questionCount', 10)
        ->call('startExam')
        ->assertSet('examStarted', true)
        ->assertSet('examFinished', false)
        ->assertSet('questions', fn ($value) => count($value) === 10);
});

it('selects answers and finishes the exam, persisting results', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $notebook = Notebook::factory()->create(['user_id' => $user->id]);
    $discipline = Discipline::factory()->create([
        'notebook_id' => $notebook->id,
        'user_id' => $user->id,
    ]);

    createFlashcards($discipline, 12, uniqueAnswers: true);

    $component = Livewire::test(SimulatedExam::class)
        ->set('scopeType', 'discipline')
        ->set('disciplineId', $discipline->id)
        ->set('questionCount', 10)
        ->call('startExam');

    $questions = $component->get('questions');

    foreach ($questions as $index => $question) {
        $optionKey = $question['options'][0]['key'];
        $component->call('selectOption', $index, $optionKey);
    }

    $component->call('finishExam')
        ->assertSet('examFinished', true)
        ->assertSet('answeredCount', 10)
        ->assertSet('completedAt', Carbon::now()->toIso8601String())
        ->assertSet('durationSeconds', 0);

    expect(SimulatedExamModel::count())->toBe(1)
        ->and(SimulatedExamModel::first()->question_count)->toBe(10);
});

function createFlashcards(Discipline $discipline, int $count, bool $uniqueAnswers = false): void
{
    for ($i = 0; $i < $count; $i++) {
        Note::factory()->create([
            'discipline_id' => $discipline->id,
            'is_flashcard' => true,
            'flashcard_question' => 'Question '.Str::uuid(),
            'flashcard_answer' => $uniqueAnswers ? 'Answer '.Str::uuid() : 'Repeated answer',
        ]);
    }
}
