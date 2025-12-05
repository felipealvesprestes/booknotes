<?php

use App\Livewire\Study\Exercises;
use App\Models\Discipline;
use App\Models\Notebook;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('it warns when no flashcards exist for the selected discipline', function (): void {
    $user = User::factory()->create();
    $notebook = Notebook::factory()->create(['user_id' => $user->id]);
    $discipline = Discipline::factory()->create([
        'notebook_id' => $notebook->id,
        'user_id' => $user->id,
    ]);

    Livewire::test(Exercises::class, ['disciplineId' => $discipline->id])
        ->assertSet('exercise', null)
        ->assertSet('warningMessage', __('Create at least one flashcard to unlock the exercises.'));
});

test('it generates a multiple choice exercise when enough flashcards exist', function (): void {
    $user = User::factory()->create();
    $notebook = Notebook::factory()->create(['user_id' => $user->id]);
    $discipline = Discipline::factory()->create([
        'notebook_id' => $notebook->id,
        'user_id' => $user->id,
    ]);

    createFlashcardsForDiscipline($discipline, 5);

    $component = Livewire::test(Exercises::class, ['disciplineId' => $discipline->id])
        ->call('switchMode', 'multiple_choice');

    $component->assertSet('mode', 'multiple_choice')
        ->assertSet('warningMessage', null)
        ->assertSet('exercise', function ($exercise) {
            return is_array($exercise)
                && ($exercise['type'] ?? null) === 'multiple_choice'
                && count($exercise['options'] ?? []) >= 2
                && isset($exercise['correct_option_key']);
        });
});

test('it registers true or false answers and updates stats', function (): void {
    $component = Livewire::test(Exercises::class);

    $component->set('exercise', [
        'type' => 'true_false',
        'statement_is_true' => true,
        'question' => 'Is Earth round?',
        'statement' => 'Earth is an oblate spheroid.',
        'correct_answer' => 'True',
    ])->call('submitTrueFalse', true)
        ->assertSet('answeredCorrectly', true)
        ->assertSet('stats', function (array $stats) {
            return $stats['answered'] === 1 && $stats['correct'] === 1;
        })
        ->assertSet('feedbackTitle', __('Great job!'));
});

test('it enforces guesses before checking fill in the blank answers', function (): void {
    $component = Livewire::test(Exercises::class);

    $component->set('mode', 'fill_blank')
        ->set('exercise', [
            'type' => 'fill_blank',
            'question' => 'Define Physics',
            'blanks' => [
                ['index' => 0, 'label' => 'Blank 1', 'answer' => 'Physics'],
            ],
            'correct_answer' => 'Physics is the study of matter.',
        ])
        ->set('fillGuesses', [''])
        ->call('submitFillBlank')
        ->assertSet('feedbackTitle', __('Type your guess before checking the answer.'))
        ->assertSet('answeredCorrectly', null);
});

test('it validates fill blank answers and registers incorrect guesses', function (): void {
    $component = Livewire::test(Exercises::class);

    $component->set('mode', 'fill_blank')
        ->set('exercise', [
            'type' => 'fill_blank',
            'question' => 'What is a neuron?',
            'blanks' => [
                ['index' => 0, 'label' => 'Blank 1', 'answer' => 'Neuron'],
            ],
            'correct_answer' => 'Neuron is a nerve cell.',
        ])
        ->set('fillGuesses', ['axons'])
        ->call('submitFillBlank')
        ->assertSet('answeredCorrectly', false)
        ->assertSet('stats', function (array $stats) {
            return $stats['answered'] === 1 && $stats['correct'] === 0;
        })
        ->assertSet('feedbackTitle', __('Keep going!'));
});

test('it records multiple choice submissions', function (): void {
    $component = Livewire::test(Exercises::class);

    $component->set('mode', 'multiple_choice')
        ->set('exercise', [
            'type' => 'multiple_choice',
            'question' => 'Capital of France?',
            'options' => [
                ['key' => 'A', 'text' => 'Berlin'],
                ['key' => 'B', 'text' => 'Paris'],
                ['key' => 'C', 'text' => 'Rome'],
            ],
            'correct_option_key' => 'B',
            'correct_answer' => 'Paris',
        ])
        ->call('submitMultipleChoice', 'B')
        ->assertSet('answeredCorrectly', true)
        ->assertSet('stats', function (array $stats) {
            return $stats['answered'] === 1 && $stats['correct'] === 1;
        });
});

test('it advances to another exercise when requested', function (): void {
    $user = User::factory()->create();
    $notebook = Notebook::factory()->create(['user_id' => $user->id]);
    $discipline = Discipline::factory()->create([
        'notebook_id' => $notebook->id,
        'user_id' => $user->id,
    ]);

    createFlashcardsForDiscipline($discipline, 4);

    $component = Livewire::test(Exercises::class, ['disciplineId' => $discipline->id]);
    $component->call('nextExercise')
        ->assertSet('exercise', fn ($exercise) => is_array($exercise));
});

function createFlashcardsForDiscipline(Discipline $discipline, int $count): void
{
    $ownerId = $discipline->user_id ?? $discipline->notebook?->user_id;

    for ($i = 0; $i < $count; $i++) {
        Note::factory()->create([
            'discipline_id' => $discipline->id,
            'is_flashcard' => true,
            'flashcard_question' => 'Question '.Str::uuid(),
            'flashcard_answer' => 'Answer '.Str::uuid(),
            'user_id' => $ownerId,
        ]);
    }
}
