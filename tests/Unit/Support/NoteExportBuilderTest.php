<?php

use App\Models\Discipline;
use App\Models\Notebook;
use App\Models\Note;
use App\Models\User;
use App\Support\NoteExportBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

use function Pest\Laravel\actingAs;

uses(TestCase::class, RefreshDatabase::class);

it('collects notes respecting scope, type and explicit user filters', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    actingAs($user);
    $notebookA = Notebook::create([
        'title' => 'Notebook A',
        'description' => null,
    ]);
    $notebookB = Notebook::create([
        'title' => 'Notebook B',
        'description' => null,
    ]);

    $math = Discipline::create([
        'title' => 'Mathematics',
        'description' => null,
        'notebook_id' => $notebookA->id,
    ]);
    $history = Discipline::create([
        'title' => 'History',
        'description' => null,
        'notebook_id' => $notebookB->id,
    ]);

    Note::create([
        'title' => 'Algebra',
        'content' => 'Linear equations',
        'is_flashcard' => false,
        'discipline_id' => $math->id,
    ]);
    Note::create([
        'title' => 'Anatomy Card',
        'content' => 'Organs overview',
        'is_flashcard' => true,
        'discipline_id' => $math->id,
    ]);
    Note::create([
        'title' => 'Botany',
        'content' => 'Plants basics',
        'is_flashcard' => false,
        'discipline_id' => $history->id,
    ]);

    actingAs($otherUser);
    $otherNotebook = Notebook::create([
        'title' => 'Other Notebook',
        'description' => null,
    ]);
    $otherDiscipline = Discipline::create([
        'title' => 'Other Discipline',
        'description' => null,
        'notebook_id' => $otherNotebook->id,
    ]);
    Note::create([
        'title' => 'Secret Note',
        'content' => 'Should not appear',
        'is_flashcard' => true,
        'discipline_id' => $otherDiscipline->id,
    ]);

    Auth::logout();

    $flashcards = NoteExportBuilder::collectNotes([
        'scope' => 'notebook',
        'selectedNotebook' => (string) $notebookA->id,
        'noteType' => 'flashcards',
    ], $user);

    expect($flashcards)->toHaveCount(1);
    expect($flashcards->pluck('title')->all())->toBe(['Anatomy Card']);

    $disciplineNotes = NoteExportBuilder::collectNotes([
        'scope' => 'discipline',
        'selectedDiscipline' => (string) $history->id,
        'noteType' => 'notes',
    ], $user);

    expect($disciplineNotes)->toHaveCount(1);
    expect($disciplineNotes->first()->title)->toBe('Botany');

    $allNotes = NoteExportBuilder::collectNotes([], $user);

    expect($allNotes->pluck('title')->all())->toBe([
        'Algebra',
        'Anatomy Card',
        'Botany',
    ]);
    expect($allNotes->pluck('title'))->not->toContain('Secret Note');
});

it('summarizes notes and flashcards correctly', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $notebook = Notebook::create([
        'title' => 'Notebook Summary',
        'description' => null,
    ]);

    $math = Discipline::create([
        'title' => 'Math',
        'description' => null,
        'notebook_id' => $notebook->id,
    ]);

    $history = Discipline::create([
        'title' => 'History',
        'description' => null,
        'notebook_id' => $notebook->id,
    ]);

    Note::create([
        'title' => 'Limits',
        'content' => 'Calculus basics',
        'is_flashcard' => false,
        'discipline_id' => $math->id,
    ]);
    Note::create([
        'title' => 'Integrals',
        'content' => 'Practice problems',
        'is_flashcard' => true,
        'discipline_id' => $math->id,
    ]);
    Note::create([
        'title' => 'World War I',
        'content' => 'Timeline',
        'is_flashcard' => false,
        'discipline_id' => $history->id,
    ]);

    $notes = NoteExportBuilder::collectNotes([], $user);
    $summary = NoteExportBuilder::summary($notes);

    expect($summary)->toMatchArray([
        'total' => 3,
        'noteCount' => 2,
        'flashcardCount' => 1,
        'disciplines' => 2,
        'notebooks' => 1,
    ]);
});

it('groups notes by discipline or notebook with fallbacks for missing relations', function (): void {
    $stemNotebook = new Notebook(['title' => 'STEM Notebook']);
    $mathDiscipline = new Discipline(['title' => 'Math']);
    $mathDiscipline->setRelation('notebook', $stemNotebook);

    $philosophyDiscipline = new Discipline(['title' => 'Philosophy']);

    $withNotebook = new Note(['title' => 'Algebra']);
    $withNotebook->setRelation('discipline', $mathDiscipline);

    $disciplineOnly = new Note(['title' => 'Ethics']);
    $disciplineOnly->setRelation('discipline', $philosophyDiscipline);

    $noDiscipline = new Note(['title' => 'Detached']);
    $noDiscipline->setRelation('discipline', null);

    $notes = collect([$withNotebook, $disciplineOnly, $noDiscipline]);

    $groupedByDiscipline = NoteExportBuilder::groupNotes($notes);

    expect($groupedByDiscipline->keys()->all())->toBe([
        __('Discipline removed'),
        'Math — STEM Notebook',
        'Philosophy',
    ]);
    expect($groupedByDiscipline['Math — STEM Notebook'])->toHaveCount(1);
    expect($groupedByDiscipline['Philosophy'])->toHaveCount(1);
    expect($groupedByDiscipline[__('Discipline removed')])->toHaveCount(1);

    $groupedByNotebook = NoteExportBuilder::groupNotes($notes, 'notebook');

    expect($groupedByNotebook->keys()->all())->toBe([
        __('Notebook removed'),
        'STEM Notebook',
    ]);
    expect($groupedByNotebook['STEM Notebook'])->toHaveCount(1);
    expect($groupedByNotebook[__('Notebook removed')])->toHaveCount(2);
});
