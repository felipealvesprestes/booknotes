<?php

namespace App\Http\Controllers;

use App\Models\NoteExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoteExportDownloadController extends Controller
{
    public function __invoke(Request $request, NoteExport $noteExport)
    {
        if ($noteExport->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $noteExport->isReady() || ! $noteExport->file_path) {
            abort(404);
        }

        $disk = config('filesystems.default');

        if (! Storage::disk($disk)->exists($noteExport->file_path)) {
            abort(404);
        }

        return Storage::disk($disk)->download($noteExport->file_path, $noteExport->file_name);
    }
}
