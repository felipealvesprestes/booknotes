<?php

use App\Http\Controllers\NoteExportDownloadController;
use App\Models\NoteExport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
    config(['filesystems.default' => 'local']);
});

it('allows the owner to download a ready export file', function (): void {
    $user = User::factory()->create();

    $export = NoteExport::factory()->create([
        'user_id' => $user->id,
        'status' => NoteExport::STATUS_COMPLETED,
        'file_path' => 'exports/notes-user.csv',
        'file_name' => 'notes.csv',
    ]);

    Storage::disk('local')->put($export->file_path, 'csv content');

    $response = $this->actingAs($user)->get(route('notes.export.download', $export));

    $response->assertOk()
        ->assertDownload('notes.csv');
});

it('forbids downloading exports that belong to another user', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $export = NoteExport::factory()->create([
        'user_id' => $owner->id,
        'status' => NoteExport::STATUS_COMPLETED,
        'file_path' => 'exports/notes-user.csv',
        'file_name' => 'notes.csv',
    ]);

    Storage::disk('local')->put($export->file_path, 'csv content');

    $response = $this->actingAs($otherUser)->get(route('notes.export.download', $export));

    $response->assertNotFound();
});

it('returns not found when the export is not ready', function (): void {
    $user = User::factory()->create();
    $export = NoteExport::factory()->create([
        'user_id' => $user->id,
        'status' => NoteExport::STATUS_PROCESSING,
        'file_path' => null,
        'file_name' => 'notes.csv',
    ]);

    $response = $this->actingAs($user)->get(route('notes.export.download', $export));

    $response->assertNotFound();
});

it('returns not found when the file is missing from storage', function (): void {
    $user = User::factory()->create();
    $export = NoteExport::factory()->create([
        'user_id' => $user->id,
        'status' => NoteExport::STATUS_COMPLETED,
        'file_path' => 'exports/missing.csv',
        'file_name' => 'notes.csv',
    ]);

    $response = $this->actingAs($user)->get(route('notes.export.download', $export));

    $response->assertNotFound();
});

it('aborts with forbidden when the export belongs to another user', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $export = NoteExport::factory()->create([
        'user_id' => $owner->id,
        'status' => NoteExport::STATUS_COMPLETED,
        'file_path' => 'exports/owner.csv',
        'file_name' => 'owner.csv',
    ]);

    $request = Request::create('/notes/export/'.$export->id, 'GET');
    $request->setUserResolver(fn () => $otherUser);

    $controller = app(NoteExportDownloadController::class);

    try {
        $controller($request, $export);
        test()->fail('Expected forbidden abort.');
    } catch (HttpException $exception) {
        expect($exception->getStatusCode())->toBe(403);
    }
});
