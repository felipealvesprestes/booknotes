<?php

use App\Models\NoteExport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function makeNoteExport(User $user, array $extra = []): NoteExport
{
    $status = $extra['status'] ?? NoteExport::STATUS_PENDING;

    $export = new NoteExport(array_merge([
        'file_name' => 'notes.pdf',
        'file_path' => $status === NoteExport::STATUS_COMPLETED ? 'exports/notes.pdf' : null,
        'note_count' => 5,
        'flashcard_count' => 2,
        'status' => $status,
        'filters' => ['scope' => 'all'],
    ], $extra));

    $export->user()->associate($user);
    $export->save();

    if (array_key_exists('created_at', $extra)) {
        $export->forceFill(['created_at' => $extra['created_at']])->saveQuietly();
    }

    return $export;
}

it('detects readiness, tone and download availability', function (): void {
    $user = User::factory()->create();

    $export = makeNoteExport($user, [
        'status' => NoteExport::STATUS_COMPLETED,
        'file_path' => 'exports/completed.pdf',
    ]);

    expect($export->isReady())->toBeTrue()
        ->and($export->statusLabel())->toBe(__('Ready'))
        ->and($export->statusTone())->toBe('success')
        ->and($export->downloadRoute())->toBe(route('notes.export.download', $export));
});

it('belongs to the user that requested the export', function (): void {
    $user = User::factory()->create();
    $export = makeNoteExport($user);

    expect($export->user)->not->toBeNull()
        ->and($export->user->is($user))->toBeTrue();
});

it('detects processing states and labels them correctly', function (): void {
    $user = User::factory()->create();

    $pending = makeNoteExport($user, ['status' => NoteExport::STATUS_PENDING]);
    $processing = makeNoteExport($user, ['status' => NoteExport::STATUS_PROCESSING]);
    $failed = makeNoteExport($user, ['status' => NoteExport::STATUS_FAILED]);
    $custom = makeNoteExport($user, ['status' => 'archived']);

    expect($pending->isProcessing())->toBeTrue()
        ->and($pending->statusLabel())->toBe(__('Queued'))
        ->and($processing->isProcessing())->toBeTrue()
        ->and($processing->statusLabel())->toBe(__('Processing'))
        ->and($failed->statusTone())->toBe('danger')
        ->and($failed->statusLabel())->toBe(__('Failed'))
        ->and($custom->statusTone())->toBe('muted')
        ->and($custom->statusLabel())->toBe('Archived');
});

it('scopes recent exports ordering by newest first', function (): void {
    $user = User::factory()->create();

    $older = makeNoteExport($user, [
        'file_name' => 'older.pdf',
        'created_at' => Carbon::now()->subDay(),
    ]);

    $newer = makeNoteExport($user, [
        'file_name' => 'newer.pdf',
        'created_at' => Carbon::now(),
    ]);

    $ordered = NoteExport::query()->recent()->pluck('file_name')->all();

    expect($ordered)->toBe(['newer.pdf', 'older.pdf']);
});
