<?php

namespace App\Support;

use App\Models\Note;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class NoteExportBuilder
{
    public static function collectNotes(array $filters, ?User $user = null): Collection
    {
        $scope = $filters['scope'] ?? 'all';
        $noteType = $filters['noteType'] ?? 'all';
        $notebookId = isset($filters['selectedNotebook']) ? (int) $filters['selectedNotebook'] : null;
        $disciplineId = isset($filters['selectedDiscipline']) ? (int) $filters['selectedDiscipline'] : null;

        $user ??= Auth::user();

        $query = Note::query()
            ->with(['discipline.notebook'])
            ->when($user, fn ($builder) => $builder->ownedBy($user))
            ->orderBy('title');

        if ($scope === 'notebook' && $notebookId) {
            $query->whereHas('discipline', function ($disciplineQuery) use ($notebookId): void {
                $disciplineQuery->where('notebook_id', $notebookId);
            });
        }

        if ($scope === 'discipline' && $disciplineId) {
            $query->where('discipline_id', $disciplineId);
        }

        if ($noteType === 'notes') {
            $query->where('is_flashcard', false);
        } elseif ($noteType === 'flashcards') {
            $query->where('is_flashcard', true);
        }

        return $query->get();
    }

    public static function summary(Collection $notes): array
    {
        return [
            'total' => $notes->count(),
            'noteCount' => $notes->where('is_flashcard', false)->count(),
            'flashcardCount' => $notes->where('is_flashcard', true)->count(),
            'disciplines' => $notes->pluck('discipline_id')->filter()->unique()->count(),
            'notebooks' => $notes->pluck('discipline.notebook_id')->filter()->unique()->count(),
        ];
    }

    public static function groupNotes(Collection $notes, string $grouping = 'discipline'): Collection
    {
        $grouping = $grouping === 'notebook' ? 'notebook' : 'discipline';

        if ($grouping === 'notebook') {
            return $notes->groupBy(function (Note $note): string {
                return $note->discipline?->notebook?->title
                    ? $note->discipline->notebook->title
                    : __('Notebook removed');
            })->sortKeys();
        }

        return $notes->groupBy(function (Note $note): string {
            $discipline = $note->discipline?->title;
            $notebook = $note->discipline?->notebook?->title;

            return $discipline
                ? ($notebook ? "{$discipline} — {$notebook}" : $discipline)
                : __('Discipline removed');
        })->sortKeys();
    }
}
