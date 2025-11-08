<?php

namespace App\Jobs;

use App\Models\NoteExport;
use App\Models\User;
use App\Support\NoteExportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateNoteExportPdf implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $noteExportId)
    {
    }

    public function handle(): void
    {
        /** @var NoteExport|null $noteExport */
        $noteExport = NoteExport::query()->find($this->noteExportId);

        if (! $noteExport) {
            return;
        }

        $noteExport->update([
            'status' => NoteExport::STATUS_PROCESSING,
            'failure_reason' => null,
        ]);

        try {
            $user = $noteExport->user;

            if (! $user instanceof User) {
                throw new \RuntimeException('Unable to resolve user for export job.');
            }

            $filters = $noteExport->filters ?? [];

            $notes = NoteExportBuilder::collectNotes($filters, $user);

            if ($notes->isEmpty()) {
                $noteExport->update([
                    'status' => NoteExport::STATUS_FAILED,
                    'failure_reason' => __('No notes match the selected filters.'),
                    'finished_at' => now(),
                ]);

                return;
            }

            $summary = NoteExportBuilder::summary($notes);

            $noteExport->update([
                'note_count' => $summary['noteCount'],
                'flashcard_count' => $summary['flashcardCount'],
            ]);

            $noteCards = $notes->where('is_flashcard', false)->values();
            $flashcardCards = $notes->where('is_flashcard', true)->values();

            $pdf = Pdf::loadView('pdf.notes-export', [
                'user' => $user,
                'summary' => $summary,
                'filters' => $filters,
                'notes' => $noteCards,
                'flashcards' => $flashcardCards,
                'generatedAt' => now(),
            ]);

            $orientation = ($filters['layoutOrientation'] ?? 'portrait') === 'landscape'
                ? 'landscape'
                : 'portrait';

            $pdf->setPaper('a4', $orientation);

            $storagePath = 'exports/' . now()->format('Y/m/') . $noteExport->file_name;

            Storage::disk(config('filesystems.default'))->put($storagePath, $pdf->output());

            $noteExport->update([
                'status' => NoteExport::STATUS_COMPLETED,
                'file_path' => $storagePath,
                'finished_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            if (isset($noteExport)) {
                $noteExport->update([
                    'status' => NoteExport::STATUS_FAILED,
                    'failure_reason' => $exception->getMessage(),
                    'finished_at' => now(),
                ]);
            }
        }
    }
}
