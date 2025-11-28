<?php

use App\Models\Discipline;
use App\Models\Note;
use App\Models\Notebook;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns zero metrics when content is empty after cleaning', function (): void {
    $note = new Note(['content' => '   ']);

    expect($note->word_count)->toBe(0)
        ->and($note->char_count)->toBe(0)
        ->and($note->reading_time)->toBe(0);
});

it('cleans html before counting text metrics', function (): void {
    $note = new Note([
        'content' => '<p>Hello&nbsp;world!</p><p>&nbsp;Study&nbsp;&amp;&nbsp;grow</p>',
    ]);

    expect($note->word_count)->toBe(5)
        ->and($note->char_count)->toBe(25)
        ->and($note->reading_time)->toBe(1);
});

it('detaches tags when syncing with an empty list', function (): void {
    $user = User::factory()->create();
    $note = createNoteForUser($user);

    $tag = Tag::create(['name' => 'Focus', 'user_id' => $user->id]);
    $note->tags()->attach($tag->id);
    expect($note->tags()->count())->toBe(1);

    $note->syncTags([]);

    expect($note->tags()->count())->toBe(0);
});

it('detaches tags when owner cannot be resolved', function (): void {
    $user = User::factory()->create();
    $note = createNoteForUser($user);

    $tag = Tag::create(['name' => 'Biology', 'user_id' => $user->id]);
    $note->tags()->attach($tag->id);
    expect($note->tags()->count())->toBe(1);

    $note->setAttribute('user_id', null);
    Auth::logout();

    $note->syncTags(['Biology']);

    expect($note->tags()->count())->toBe(0);
});

it('syncs normalized tags, reusing existing ones and creating new entries', function (): void {
    $user = User::factory()->create();
    $note = createNoteForUser($user);

    $focus = Tag::create(['name' => 'Focus', 'user_id' => $user->id]);
    $note->tags()->attach($focus->id);

    $note->syncTags([' Focus ', 'focus', 'Deep Work']);
    $note->refresh();

    $tagNames = $note->tags()->pluck('name')->sort()->values()->all();

    expect($tagNames)->toBe(['Deep Work', 'Focus']);
    expect(Tag::where('name', 'Deep Work')->where('user_id', $user->id)->exists())->toBeTrue();
});

it('finds existing tags case-insensitively for an owner', function (): void {
    $user = User::factory()->create();
    $tag = Tag::create(['name' => 'Pomodoro', 'user_id' => $user->id]);

    $testable = new class extends Note
    {
        public function callFindExistingTagForOwner(int $ownerId, string $tagName, string $lower): ?Tag
        {
            return $this->findExistingTagForOwner($ownerId, $tagName, $lower);
        }
    };

    $found = $testable->callFindExistingTagForOwner($user->id, 'POMODORO', 'pomodoro');

    expect($found)->not->toBeNull()
        ->and($found->is($tag))->toBeTrue();
});

it('retries duplicate tag creations by returning existing row', function (): void {
    $user = User::factory()->create();
    $tag = Tag::create(['name' => 'Deep', 'user_id' => $user->id]);

    $testable = new class extends Note
    {
        public function attemptCreateTagForOwner(int $ownerId, string $tagName, string $lower): Tag
        {
            return $this->createTagForOwner($ownerId, $tagName, $lower);
        }
    };

    $exception = makeTagQueryException();

    $shouldThrow = true;
    $listener = function () use (&$shouldThrow, $exception): void {
        if ($shouldThrow) {
            $shouldThrow = false;
            throw $exception;
        }
    };

    Event::listen('eloquent.creating: '.Tag::class, $listener);

    try {
        $result = $testable->attemptCreateTagForOwner($user->id, 'Deep', 'deep');
    } finally {
        Event::forget('eloquent.creating: '.Tag::class);
    }

    expect($result)->not->toBeNull()
        ->and($result->is($tag))->toBeTrue();
});

it('rethrows when tag creation fails with a non duplicate constraint', function (): void {
    $user = User::factory()->create();

    $testable = new class extends Note
    {
        public function attemptCreateTagForOwner(int $ownerId, string $tagName, string $lower): Tag
        {
            return $this->createTagForOwner($ownerId, $tagName, $lower);
        }
    };

    $exception = makeTagQueryException(
        1452,
        'Cannot add or update a child row: a foreign key constraint fails'
    );

    Event::listen('eloquent.creating: '.Tag::class, function () use ($exception): void {
        throw $exception;
    });

    try {
        expect(fn () => $testable->attemptCreateTagForOwner($user->id, 'Focus', 'focus'))
            ->toThrow(QueryException::class);
    } finally {
        Event::forget('eloquent.creating: '.Tag::class);
    }
});

it('rethrows when duplicate constraint occurs but tag cannot be found afterwards', function (): void {
    $user = User::factory()->create();

    $testable = new class extends Note
    {
        public function attemptCreateTagForOwner(int $ownerId, string $tagName, string $lower): Tag
        {
            return $this->createTagForOwner($ownerId, $tagName, $lower);
        }
    };

    $exception = makeTagQueryException();

    Event::listen('eloquent.creating: '.Tag::class, function () use ($exception): void {
        throw $exception;
    });

    try {
        expect(fn () => $testable->attemptCreateTagForOwner($user->id, 'Unique', 'unique'))
            ->toThrow(QueryException::class);
    } finally {
        Event::forget('eloquent.creating: '.Tag::class);
    }
});

function createNoteForUser(User $user, array $attributes = []): Note
{
    $notebook = new Notebook([
        'title' => 'Notebook',
        'description' => null,
    ]);
    $notebook->user()->associate($user);
    $notebook->save();

    $discipline = new Discipline([
        'title' => 'Discipline',
        'description' => null,
        'notebook_id' => $notebook->id,
    ]);
    $discipline->user()->associate($user);
    $discipline->save();

    $note = new Note(array_merge([
        'title' => 'Example Note',
        'content' => 'Sample content for metrics.',
        'is_flashcard' => false,
        'flashcard_question' => null,
        'flashcard_answer' => null,
    ], $attributes));

    $note->user()->associate($user);
    $note->discipline()->associate($discipline);
    $note->save();

    return $note->fresh();
}

function makeTagQueryException(int $sqlError = 1062, string $message = 'Integrity constraint violation: 1062 Duplicate entry for key tags_user_id_name_unique'): QueryException
{
    $pdoException = new \PDOException($message, $sqlError);
    $pdoException->errorInfo = ['23000', $sqlError, $message];

    return new QueryException(
        config('database.default'),
        'insert into "tags" values (?, ?)',
        [],
        $pdoException
    );
}
