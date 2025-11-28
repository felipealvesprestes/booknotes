<?php

use App\Jobs\GenerateNoteExportPdf;
use App\Models\Discipline;
use App\Models\Note;
use App\Models\NoteExport;
use App\Models\Notebook;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('stores the generated pdf and updates the export summary', function (): void {
    $disk = config('filesystems.default');
    Storage::fake($disk);

    $now = Carbon::parse('2024-05-01 12:00:00');
    Carbon::setTestNow($now);

    $user = User::factory()->create();
    $discipline = createDisciplineForExport($user);
    createNoteForExport($user, $discipline, [
        'title' => 'Biology',
        'is_flashcard' => false,
    ]);
    createNoteForExport($user, $discipline, [
        'title' => 'Chemistry card',
        'is_flashcard' => true,
        'flashcard_question' => 'Q',
        'flashcard_answer' => 'A',
    ]);

    $export = NoteExport::create([
        'user_id' => $user->id,
        'file_name' => 'notes.pdf',
        'filters' => [],
        'status' => NoteExport::STATUS_PENDING,
    ]);

    $pdfMock = Mockery::mock('Barryvdh\DomPDF\PDF');

    Pdf::shouldReceive('loadView')
        ->once()
        ->with('pdf.notes-export', Mockery::type('array'))
        ->andReturn($pdfMock);

    $pdfMock->shouldReceive('setPaper')
        ->once()
        ->with('a4', 'portrait');

    $pdfMock->shouldReceive('output')
        ->once()
        ->andReturn('fake-pdf-binary');

    (new GenerateNoteExportPdf($export->id))->handle();

    $export->refresh();
    expect($export->status)->toBe(NoteExport::STATUS_COMPLETED)
        ->and($export->file_path)->toBe('exports/'.$now->format('Y/m/').'notes.pdf')
        ->and($export->note_count)->toBe(1)
        ->and($export->flashcard_count)->toBe(1)
        ->and($export->finished_at)->not->toBeNull();

    Storage::disk($disk)->assertExists($export->file_path);

    Carbon::setTestNow();
});

it('sets paper orientation to landscape when requested', function (): void {
    Storage::fake(config('filesystems.default'));

    $user = User::factory()->create();
    $discipline = createDisciplineForExport($user);
    createNoteForExport($user, $discipline, ['is_flashcard' => false]);

    $export = NoteExport::create([
        'user_id' => $user->id,
        'file_name' => 'landscape.pdf',
        'filters' => ['layoutOrientation' => 'landscape'],
        'status' => NoteExport::STATUS_PENDING,
    ]);

    $pdfMock = Mockery::mock('Barryvdh\DomPDF\PDF');

    Pdf::shouldReceive('loadView')
        ->once()
        ->with('pdf.notes-export', Mockery::type('array'))
        ->andReturn($pdfMock);

    $pdfMock->shouldReceive('setPaper')
        ->once()
        ->with('a4', 'landscape');

    $pdfMock->shouldReceive('output')->once()->andReturn('binary');

    (new GenerateNoteExportPdf($export->id))->handle();

    $export->refresh();

    expect($export->status)->toBe(NoteExport::STATUS_COMPLETED)
        ->and($export->file_path)->toContain('landscape.pdf');
});

it('marks the export as failed when no notes match the filters', function (): void {
    Storage::fake(config('filesystems.default'));

    $user = User::factory()->create();

    $export = NoteExport::create([
        'user_id' => $user->id,
        'file_name' => 'empty.pdf',
        'filters' => ['scope' => 'discipline', 'selectedDiscipline' => 999],
        'status' => NoteExport::STATUS_PENDING,
    ]);

    Pdf::shouldReceive('loadView')->never();

    (new GenerateNoteExportPdf($export->id))->handle();

    $export->refresh();

    expect($export->status)->toBe(NoteExport::STATUS_FAILED)
        ->and($export->failure_reason)->toBe(__('No notes match the selected filters.'))
        ->and($export->finished_at)->not->toBeNull();
});

it('records the exception when pdf generation fails', function (): void {
    Storage::fake(config('filesystems.default'));

    $user = User::factory()->create();
    $discipline = createDisciplineForExport($user);
    createNoteForExport($user, $discipline, [
        'title' => 'Physics',
        'is_flashcard' => false,
    ]);

    $export = NoteExport::create([
        'user_id' => $user->id,
        'file_name' => 'broken.pdf',
        'filters' => [],
        'status' => NoteExport::STATUS_PENDING,
    ]);

    Pdf::shouldReceive('loadView')
        ->once()
        ->andThrow(new RuntimeException('PDF failed'));

    (new GenerateNoteExportPdf($export->id))->handle();

    $export->refresh();

    expect($export->status)->toBe(NoteExport::STATUS_FAILED)
        ->and($export->failure_reason)->toBe('PDF failed')
        ->and($export->finished_at)->not->toBeNull();
});

function createDisciplineForExport(User $user): Discipline
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

    return $discipline;
}

function createNoteForExport(User $user, Discipline $discipline, array $attributes = []): Note
{
    $note = new Note(array_merge([
        'title' => 'Note',
        'content' => 'Content',
        'is_flashcard' => false,
        'flashcard_question' => null,
        'flashcard_answer' => null,
        'discipline_id' => $discipline->id,
    ], $attributes));

    $note->user()->associate($user);
    $note->save();

    return $note;
}
it('does nothing when the export cannot be found', function (): void {
    Pdf::shouldReceive('loadView')->never();
    Storage::fake(config('filesystems.default'));

    expect(fn () => (new GenerateNoteExportPdf(999))->handle())->not->toThrow(Throwable::class);
});

it('fails when the export owner cannot be resolved', function (): void {
    Storage::fake(config('filesystems.default'));

    $user = User::factory()->create();

    $export = NoteExport::create([
        'user_id' => $user->id,
        'file_name' => 'orphan.pdf',
        'filters' => [],
        'status' => NoteExport::STATUS_PENDING,
    ]);

    Event::listen('eloquent.retrieved: '.NoteExport::class, function (NoteExport $model) use ($export): void {
        if ($model->getKey() === $export->getKey()) {
            $model->setRelation('user', null);
        }
    });

    Pdf::shouldReceive('loadView')->never();

    try {
        (new GenerateNoteExportPdf($export->id))->handle();
    } finally {
        Event::forget('eloquent.retrieved: '.NoteExport::class);
    }

    $export->refresh();

    expect($export->status)->toBe(NoteExport::STATUS_FAILED)
        ->and($export->failure_reason)->toBe('Unable to resolve user for export job.');
});
