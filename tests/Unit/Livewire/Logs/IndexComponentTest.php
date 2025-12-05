<?php

use App\Livewire\Logs\Index;
use App\Models\Discipline;
use App\Models\FlashcardSession;
use App\Models\Log;
use App\Models\Note;
use App\Models\Notebook;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

uses(TestCase::class);

class IndexComponentTestDouble extends Index
{
    public function describe(Log $log, array $related): string
    {
        return $this->descriptionForLog($log, $related);
    }

    public function meta(Log $log, array $related): array
    {
        return $this->metaForLog($log, $related);
    }

    public function tags(Log $log, array $related): array
    {
        return $this->tagsForLog($log, $related);
    }

    public function changes(array $before, array $after, array $related): array
    {
        return $this->formatChanges($before, $after, $related);
    }

    public function changesFromLog(Log $log, array $related): array
    {
        return $this->changesForLog($log, $related);
    }

    public function valueFor(string $key, $value, array $related): string
    {
        return $this->formatValueForKey($key, $value, $related);
    }

    public function labelFor(string $key): string
    {
        return $this->labelForKey($key);
    }

    public function text(string $value): string
    {
        return $this->expandedText($value);
    }

    public function labelForActionPublic(string $action): string
    {
        return $this->labelForAction($action);
    }

    public function iconForActionPublic(string $action): string
    {
        return $this->iconForAction($action);
    }

    public function toneForActionPublic(string $action): string
    {
        return $this->toneClassesForAction($action);
    }

    public function tagPublic(string $text, string $tone): array
    {
        return $this->tag($text, $tone);
    }

    public function unique(array $values): array
    {
        return $this->uniqueIds($values);
    }
}

test('description builder covers all major actions', function (): void {
    $component = new IndexComponentTestDouble();

$note = new Note(['title' => 'Biology']);
$note->id = 1;
$discipline = new Discipline(['title' => 'Human Body']);
$discipline->id = 2;
$notebook = new Notebook(['title' => 'Science']);
$notebook->id = 3;
$session = new FlashcardSession();
$session->id = 4;
$session->setRelation('discipline', $discipline);

    $related = [
        'notes' => Collection::make([$note])->keyBy('id'),
        'disciplines' => Collection::make([$discipline])->keyBy('id'),
        'notebooks' => Collection::make([$notebook])->keyBy('id'),
        'sessions' => Collection::make([$session])->keyBy('id'),
    ];

    $cases = [
        ['note.created', ['note_id' => 1]],
        ['note.updated', ['note_id' => 1]],
        ['note.deleted', ['title' => 'Deleted']],
        ['note.deleted', ['note_id' => 1]],
        ['note.converted_to_flashcard', ['note_id' => 1]],
        ['note.reverted_from_flashcard', ['note_id' => 1]],
        ['discipline.created', ['title' => 'New Discipline']],
        ['discipline.updated', ['after' => ['title' => 'Renamed']]],
        ['discipline.updated', ['before' => ['title' => 'Old Name']]],
        ['discipline.deleted', ['title' => 'Old Discipline']],
        ['notebook.created', ['title' => 'Notebook']],
        ['notebook.updated', ['after' => ['title' => 'Updated Notebook']]],
        ['notebook.updated', ['before' => ['title' => 'Legacy Notebook']]],
        ['notebook.deleted', ['title' => 'Old Notebook']],
        ['flashcard.session_started', ['session_id' => 4, 'total_cards' => 5]],
        ['flashcard.session_started', ['session_id' => 4]],
        ['flashcard.session_started', ['discipline_id' => 2]],
        ['flashcard.session_started', ['session_id' => 4, 'total_cards' => null]],
        ['flashcard.session_started', ['total_cards' => 2]],
        ['flashcard.session_started', []],
        ['flashcard.answered', ['result' => 'correct']],
        ['flashcard.answered', ['result' => 'incorrect']],
        ['unknown.action', []],
    ];

    foreach ($cases as [$action, $context]) {
        $log = Log::make(['action' => $action, 'context' => $context, 'created_at' => Carbon::now()]);
        expect($component->describe($log, $related))->not->toBe('');
    }
});

test('description for flashcard session includes discipline and card count', function (): void {
    $component = new IndexComponentTestDouble();

    $discipline = new Discipline(['title' => 'Physics']);
    $discipline->id = 5;
    $session = new FlashcardSession();
    $session->id = 7;
    $session->setRelation('discipline', $discipline);

    $related = [
        'notes' => Collection::make(),
        'disciplines' => Collection::make([$discipline])->keyBy('id'),
        'notebooks' => Collection::make(),
        'sessions' => Collection::make([$session])->keyBy('id'),
    ];

    $log = Log::make([
        'action' => 'flashcard.session_started',
        'context' => [
            'session_id' => 7,
            'discipline_id' => 5,
            'total_cards' => 5,
        ],
        'created_at' => Carbon::now(),
    ]);

    expect($component->describe($log, $related))->toContain(__('Started a session with :count cards for “:discipline”.', [
        'count' => 5,
        'discipline' => 'Physics',
    ]));
});

test('meta builder handles various scenarios', function (): void {
    $component = new IndexComponentTestDouble();

$note = new Note(['title' => 'Thermodynamics']);
$note->id = 10;
$discipline = new Discipline(['title' => 'Physics']);
$discipline->id = 20;
$notebook = new Notebook(['title' => 'STEM']);
$notebook->id = 30;
$session = new FlashcardSession();
$session->id = 40;
$session->setRelation('discipline', $discipline);

    $related = [
        'notes' => Collection::make([$note])->keyBy('id'),
        'disciplines' => Collection::make([$discipline])->keyBy('id'),
        'notebooks' => Collection::make([$notebook])->keyBy('id'),
        'sessions' => Collection::make([$session])->keyBy('id'),
    ];

    $metaLogs = [
        ['note.converted_to_flashcard', ['note_id' => 10, 'discipline_id' => 20, 'is_flashcard' => true]],
        ['note.converted_to_flashcard', ['note_id' => 10]],
        ['note.reverted_from_flashcard', ['note_id' => 10]],
        ['discipline.updated', ['after' => ['title' => 'Advanced', 'notebook_id' => 30]]],
        ['notebook.updated', ['after' => ['title' => 'Daily']]],
        ['flashcard.session_started', ['session_id' => 40, 'total_cards' => 15]],
        ['flashcard.answered', ['note_id' => 10, 'session_id' => 40]],
    ];

    foreach ($metaLogs as [$action, $context]) {
        $log = Log::make(['action' => $action, 'context' => $context]);
        expect($component->meta($log, $related))->not->toBeEmpty();
    }
});

test('tags builder emits highlight labels', function (): void {
    $component = new IndexComponentTestDouble();

    $logFlashcard = Log::make(['action' => 'note.converted_to_flashcard', 'context' => []]);
    expect($component->tags($logFlashcard, []))->not->toBeEmpty();

    $logAnswered = Log::make(['action' => 'flashcard.answered', 'context' => ['result' => 'incorrect']]);
    $tags = $component->tags($logAnswered, []);
    expect($tags)->toHaveCount(1);

    $logCreatedFlashcard = Log::make(['action' => 'note.created', 'context' => ['is_flashcard' => true]]);
    expect($component->tags($logCreatedFlashcard, []))->not->toBeEmpty();

    $logReverted = Log::make(['action' => 'note.reverted_from_flashcard', 'context' => []]);
    expect($component->tags($logReverted, []))->not->toBeEmpty();

    $logAnsweredCorrect = Log::make(['action' => 'flashcard.answered', 'context' => ['result' => 'correct']]);
    expect($component->tags($logAnsweredCorrect, []))->toHaveCount(1);
});

test('change and value formatters render readable entries', function (): void {
    $component = new IndexComponentTestDouble();

$noteModel = new Note(['title' => 'Note']);
$noteModel->id = 1;
$disciplineModel = new Discipline(['title' => 'Discipline']);
$disciplineModel->id = 2;
$notebookModel = new Notebook(['title' => 'Notebook']);
$notebookModel->id = 3;

$related = [
    'notes' => Collection::make([$noteModel])->keyBy('id'),
    'disciplines' => Collection::make([$disciplineModel])->keyBy('id'),
    'notebooks' => Collection::make([$notebookModel])->keyBy('id'),
    'sessions' => Collection::make(),
];

    $changes = $component->changes(
        ['title' => 'Old', 'is_flashcard' => false, 'description' => 'First'],
        ['title' => 'New', 'is_flashcard' => true, 'description' => 'First'],
        $related,
    );
    expect($changes)->toHaveCount(2);

    expect($component->valueFor('notebook_id', 3, $related))->toBe('Notebook')
        ->and($component->valueFor('discipline_id', 2, $related))->toBe('Discipline')
        ->and($component->valueFor('note_id', 1, $related))->toBe('Note')
        ->and($component->valueFor('is_flashcard', false, $related))->toBe(__('Note'))
        ->and($component->valueFor('content', '<p>Content</p>', $related))->toBe('Content')
        ->and($component->valueFor('description', '<p>Desc</p>', $related))->toBe('Desc')
        ->and($component->valueFor('flashcard_question', '<p>Q</p>', $related))->toBe('Q')
        ->and($component->valueFor('flashcard_answer', '<p>A</p>', $related))->toBe('A')
        ->and($component->valueFor('custom', true, $related))->toBe(__('Yes'))
        ->and($component->valueFor('custom', null, $related))->toBe('—');
});

test('helper mappings and tags resolve to expected labels', function (): void {
    $component = new IndexComponentTestDouble();

    expect($component->labelFor('title'))->toBe(__('Title'))
        ->and($component->labelFor('content'))->toBe(__('Content'))
        ->and($component->text(" Hello \nworld "))->toBe('Hello world')
        ->and($component->labelForActionPublic('note.created'))->toBe(__('Note created'))
        ->and($component->iconForActionPublic('note.deleted'))->toBe('trash')
        ->and($component->toneForActionPublic('flashcard.session_started'))->toBe('bg-amber-100 text-amber-600')
        ->and($component->tagPublic('Info', 'blue')['text'])->toBe('Info');

    $actions = [
        'note.updated',
        'note.deleted',
        'note.converted_to_flashcard',
        'note.reverted_from_flashcard',
        'discipline.created',
        'discipline.updated',
        'discipline.deleted',
        'notebook.created',
        'notebook.updated',
        'notebook.deleted',
        'flashcard.answered',
        'flashcard.session_started',
    ];

    foreach ($actions as $action) {
        $component->labelForActionPublic($action);
        $component->iconForActionPublic($action);
        $component->toneForActionPublic($action);
    }

    expect($component->labelFor('description'))->toBe(__('Description'))
        ->and($component->labelFor('notebook_id'))->toBe(__('Notebook'))
        ->and($component->labelFor('discipline_id'))->toBe(__('Discipline'))
        ->and($component->labelFor('flashcard_question'))->toBe(__('Flashcard question'))
        ->and($component->labelFor('flashcard_answer'))->toBe(__('Flashcard answer'));
});

test('unique helper filters null and duplicate ids', function (): void {
    $component = new IndexComponentTestDouble();
    expect($component->unique([1, null, 2, 2, '', 3]))->toBe([1, 2, 3]);
});

test('mount and pagination setters normalize inputs', function (): void {
    $component = new Index();
    $component->perPage = 999;
    $component->actionFilter = '';
    $component->mount();

    expect($component->perPage)->toBe(10)
        ->and($component->actionFilter)->toBeNull();

    $component->updatedPerPage(999);
    expect($component->perPage)->toBe(10);

    $component->updatedPerPage(30);
    expect($component->perPage)->toBe(30);
});

test('changes resolver returns empty when context shape is invalid', function (): void {
    $component = new IndexComponentTestDouble();

    $log = Log::make([
        'context' => [
            'before' => 'invalid',
            'after' => ['title' => 'New'],
        ],
    ]);

    expect($component->changesFromLog($log, []))->toBeArray()->toBeEmpty();
});
