<?php

namespace App\Http\Controllers;

use App\Models\PdfDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PdfDocumentStreamController extends Controller
{
    /**
     * Stream the requested PDF to the browser after checking ownership.
     */
    public function __invoke(PdfDocument $pdfDocument): BinaryFileResponse
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($pdfDocument->path)) {
            abort(404);
        }

        PdfDocument::withoutTimestamps(function () use ($pdfDocument): void {
            $pdfDocument->forceFill(['last_opened_at' => now()])->save();
        });

        return response()->file(
            $disk->path($pdfDocument->path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$pdfDocument->original_name.'"',
            ],
        );
    }
}
