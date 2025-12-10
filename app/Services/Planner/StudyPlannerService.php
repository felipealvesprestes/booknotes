<?php

namespace App\Services\Planner;

use App\Models\Discipline;
use App\Models\StudyPlan;
use App\Models\StudyPlanDiscipline;
use App\Models\StudyPlanTask;
use App\Models\User;
use App\Models\Note;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StudyPlannerService
{
    public const STUDY_MODES = [
        'flashcards',
        'true_false',
        'fill_blank',
        'multiple_choice',
        'simulated',
    ];

    /**
     * Suggested load, used to pre-fill each task.
     *
     * @var array<string, array{quantity: int, unit: string}>
     */
    protected array $modeLoadMap = [
        'flashcards' => ['quantity' => 20, 'unit' => 'cards'],
        'true_false' => ['quantity' => 10, 'unit' => 'questions'],
        'fill_blank' => ['quantity' => 6, 'unit' => 'gaps'],
        'multiple_choice' => ['quantity' => 8, 'unit' => 'questions'],
        'simulated' => ['quantity' => 1, 'unit' => 'exam'],
    ];

    public function getOrCreatePlan(User $user): StudyPlan
    {
        return StudyPlan::firstOrCreate(
            ['user_id' => $user->id],
            [
                'study_days_per_week' => 5,
                'daily_sessions_target' => 0,
                'last_mode_index' => 0,
            ],
        );
    }

    public function findPlan(User $user): ?StudyPlan
    {
        return StudyPlan::query()
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Synchronize plan metadata and included disciplines.
     *
     * @param  array<int, int>  $disciplineSessions  [disciplineId => sessions_per_week]
     */
    public function syncPlan(
        User $user,
        int $studyDaysPerWeek,
        int $dailySessionsTarget,
        array $disciplineSessions,
    ): StudyPlan {
        $plan = $this->getOrCreatePlan($user);

        $plan->forceFill([
            'study_days_per_week' => $this->clamp($studyDaysPerWeek, 1, 7),
            'daily_sessions_target' => $this->clamp($dailySessionsTarget, 0, 12),
        ])->save();

        $requestedIds = array_map('intval', array_keys($disciplineSessions));

        if ($requestedIds === []) {
            $plan->disciplines()->delete();

            $this->clearFuturePendingTasks($user);

            return $plan->fresh('disciplines.discipline');
        }

        $validDisciplineIds = Discipline::query()
            ->whereIn('id', $requestedIds)
            ->pluck('id')
            ->all();

        $existingIds = [];

        foreach ($validDisciplineIds as $disciplineId) {
            $sessions = $this->clamp((int) ($disciplineSessions[$disciplineId] ?? 1), 1, 21);

            /** @var StudyPlanDiscipline $record */
            $record = $plan->disciplines()->updateOrCreate(
                ['discipline_id' => $disciplineId],
                ['sessions_per_week' => $sessions],
            );

            $existingIds[] = $record->discipline_id;
        }

        if ($existingIds !== []) {
            $plan->disciplines()
                ->whereNotIn('discipline_id', $existingIds)
                ->delete();
        } else {
            $plan->disciplines()->delete();
        }

        $this->clearFuturePendingTasks($user);

        return $plan->fresh('disciplines.discipline');
    }

    /**
     * Ensure tasks exist for a specific day (and returns them ordered).
     *
     * @return Collection<int, StudyPlanTask>
     */
    public function ensureDailyTasks(User $user, Carbon $date): Collection
    {
        $plan = $this->findPlan($user)?->loadMissing('disciplines');

        if (! $plan || $plan->disciplines->isEmpty()) {
            return collect();
        }

        $day = $date->copy()->startOfDay();

        $flashcardCounts = Note::query()
            ->selectRaw('discipline_id, COUNT(*) as total')
            ->where('is_flashcard', true)
            ->whereIn('discipline_id', $plan->disciplines->pluck('discipline_id'))
            ->groupBy('discipline_id')
            ->pluck('total', 'discipline_id');

        $todayTasks = StudyPlanTask::query()
            ->ownedBy($user)
            ->whereDate('scheduled_for', $day)
            ->get()
            ->groupBy('study_plan_discipline_id');

        $existingCount = $todayTasks->flatten()->count();
        $limit = null;
        // Rotate study modes so each day starts with a different modality.
        $modeIndex = $plan->last_mode_index % count(self::STUDY_MODES);

        $orderedDisciplines = $plan->disciplines
            ->loadMissing('discipline')
            ->sortBy(fn(StudyPlanDiscipline $discipline) => $this->orderKey($discipline, $day, $user));

        $created = 0;

        foreach ($orderedDisciplines as $planDiscipline) {
            $availableFlashcards = (int) ($flashcardCounts[$planDiscipline->discipline_id] ?? 0);

            if ($availableFlashcards <= 0) {
                continue;
            }

            $perDayTarget = $planDiscipline->dailyTarget($plan->study_days_per_week);
            $currentCount = isset($todayTasks[$planDiscipline->id]) ? $todayTasks[$planDiscipline->id]->count() : 0;
            $missing = max(0, $perDayTarget - $currentCount);

            while ($missing > 0 && ($limit === null || ($existingCount + $created) < $limit)) {
                $mode = self::STUDY_MODES[$modeIndex % count(self::STUDY_MODES)];
                $payload = $this->taskPayload($user, $plan, $planDiscipline, $mode, $day, $availableFlashcards);

                if ($payload === null) {
                    break;
                }

                StudyPlanTask::create($payload);

                $modeIndex++;
                $missing--;
                $created++;
            }
        }

        if ($created > 0) {
            $plan->forceFill([
                'last_mode_index' => $modeIndex % count(self::STUDY_MODES),
            ])->save();
        }

        return StudyPlanTask::query()
            ->ownedBy($user)
            ->with('discipline')
            ->whereDate('scheduled_for', $day)
            ->orderBy('scheduled_for')
            ->orderBy('id')
            ->get();
    }

    public function backlogTasks(User $user, Carbon $date): Collection
    {
        return StudyPlanTask::query()
            ->ownedBy($user)
            ->with('discipline')
            ->where('status', StudyPlanTask::STATUS_PENDING)
            ->whereDate('scheduled_for', '<', $date->copy()->startOfDay())
            ->orderBy('scheduled_for')
            ->orderBy('id')
            ->get();
    }

    public function upcomingTasks(User $user, Carbon $fromDate, int $days): Collection
    {
        if ($days <= 0) {
            return collect();
        }

        $start = $fromDate->copy()->addDay()->startOfDay();
        $end = $fromDate->copy()->addDays($days)->endOfDay();

        return StudyPlanTask::query()
            ->ownedBy($user)
            ->with('discipline')
            ->where('status', StudyPlanTask::STATUS_PENDING)
            ->whereBetween('scheduled_for', [$start->toDateString(), $end->toDateString()])
            ->orderBy('scheduled_for')
            ->orderBy('id')
            ->get();
    }

    protected function taskPayload(
        User $user,
        StudyPlan $plan,
        StudyPlanDiscipline $planDiscipline,
        string $mode,
        Carbon $day,
        int $availableFlashcards,
    ): ?array {
        $load = $this->modeLoadMap[$mode] ?? ['quantity' => 10, 'unit' => 'items'];

        if ($mode === 'simulated') {
            $load['quantity'] = 1;
        } else {
            if ($availableFlashcards <= 0) {
                return null;
            }

            $load['quantity'] = min($load['quantity'], $availableFlashcards);
        }

        return [
            'user_id' => $user->id,
            'study_plan_id' => $plan->id,
            'study_plan_discipline_id' => $planDiscipline->id,
            'discipline_id' => $planDiscipline->discipline_id,
            'study_mode' => $mode,
            'quantity' => $load['quantity'],
            'unit_label' => $load['unit'],
            'scheduled_for' => $day->toDateString(),
            'status' => StudyPlanTask::STATUS_PENDING,
        ];
    }

    protected function orderKey(StudyPlanDiscipline $discipline, Carbon $day, User $user): int
    {
        $payload = implode('-', [
            $day->format('Ymd'),
            $user->id,
            $discipline->discipline_id,
        ]);

        return (int) crc32($payload);
    }

    protected function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }

    protected function clearFuturePendingTasks(User $user): void
    {
        StudyPlanTask::query()
            ->ownedBy($user)
            ->where('status', StudyPlanTask::STATUS_PENDING)
            ->whereDate('scheduled_for', '>', now()->toDateString())
            ->delete();
    }
}
