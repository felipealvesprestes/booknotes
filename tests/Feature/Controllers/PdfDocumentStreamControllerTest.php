<?php

use App\Http\Controllers\PdfDocumentStreamController;
use App\Models\PdfDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
});

it('streams a pdf file and updates the last opened timestamp', function (): void {
    $user = User::factory()->create();

    $document = createPdfDocument($user, [
        'path' => 'pdfs/documents/sample.pdf',
        'original_name' => 'sample.pdf',
    ]);

    Storage::disk('local')->put($document->path, 'pdf binary content');

    $this->actingAs($user);

    $controller = app(PdfDocumentStreamController::class);
    $response = $controller($document);

    expect($response)->toBeInstanceOf(BinaryFileResponse::class)
        ->and($response->headers->get('Content-Type'))->toBe('application/pdf')
        ->and($response->headers->get('Content-Disposition'))->toContain('inline; filename="sample.pdf"')
        ->and($document->fresh()->last_opened_at)->not->toBeNull();
});

it('aborts with not found when the pdf file is missing', function (): void {
    $user = User::factory()->create();

    $document = createPdfDocument($user, [
        'path' => 'pdfs/documents/missing.pdf',
        'original_name' => 'missing.pdf',
    ]);

    $controller = app(PdfDocumentStreamController::class);

    try {
        $controller($document);
        test()->fail('Expected controller to abort with 404.');
    } catch (HttpException $exception) {
        expect($exception->getStatusCode())->toBe(404);
    }
});

function createPdfDocument(User $user, array $attributes = []): PdfDocument
{
    $document = new PdfDocument(array_merge([
        'title' => 'Sample PDF',
        'original_name' => 'sample.pdf',
        'path' => 'pdfs/documents/sample.pdf',
        'size' => 1024,
    ], $attributes));

    $document->user()->associate($user);
    $document->save();

    return $document->fresh();
}
