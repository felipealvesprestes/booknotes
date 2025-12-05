<?php

use App\Livewire\Pdfs\Library;
use App\Models\Log;
use App\Models\PdfDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
});

it('paginates and filters pdf documents', function (): void {
    $user = User::factory()->create();
    PdfDocument::factory()->count(2)->create([
        'user_id' => $user->id,
        'title' => 'Alpha Guide',
    ]);
    PdfDocument::factory()->create([
        'user_id' => $user->id,
        'title' => 'Beta Manual',
    ]);

    Livewire::actingAs($user)
        ->test(Library::class)
        ->set('search', 'Beta')
        ->set('perPage', 25)
        ->assertViewHas('pdfs', fn ($paginator) => $paginator->total() === 1 && $paginator->first()->title === 'Beta Manual');
});

it('uploads a pdf and logs the action', function (): void {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    Livewire::actingAs($user)
        ->test(Library::class)
        ->set('upload', $file)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showUploadSuccess', true);

    $this->assertDatabaseHas('pdf_documents', [
        'user_id' => $user->id,
        'original_name' => 'document.pdf',
    ]);
    $this->assertDatabaseHas('logs', [
        'action' => 'pdf.uploaded',
    ]);
});

it('selects and deletes a pdf document', function (): void {
    $user = User::factory()->create();
    $pdf = PdfDocument::factory()->create([
        'user_id' => $user->id,
        'path' => 'pdfs/'.$user->id.'/file.pdf',
    ]);

    Storage::disk('local')->put($pdf->path, 'content');

    Livewire::actingAs($user)
        ->test(Library::class)
        ->call('selectPdf', $pdf->id)
        ->assertSet('selectedPdfId', $pdf->id)
        ->call('deletePdf', $pdf->id)
        ->assertSet('selectedPdfId', null);

    Storage::disk('local')->assertMissing($pdf->path);
    $this->assertDatabaseMissing('pdf_documents', ['id' => $pdf->id]);
    $this->assertDatabaseHas('logs', [
        'action' => 'pdf.deleted',
    ]);
});
