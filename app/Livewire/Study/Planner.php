<?php

namespace App\Livewire\Study;

use App\Models\StudyPlan;
use App\Models\StudyPlanTask;
use App\Services\Planner\StudyPlannerService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Planner extends Component
{
    public array $planForm = [
        'study_days_per_week' => 5,
        'daily_sessions_target' => 0,
        'selected_disciplines' => [],
        'sessions_per_week' => [],
    ];

    public array $planSummary = [
        'disciplines' => 0,
        'weekly_sessions' => 0,
        'today_pending' => 0,
        'today_completed' => 0,
    ];

    /** @var Collection<int, StudyPlanTask> */
    public Collection $todayTasks;

    /** @var Collection<int, StudyPlanTask> */
    public Collection $upcomingTasks;

    public int $upcomingDays = 0;

    protected StudyPlannerService $planner;

    public bool $showResetConfirm = false;

    public function boot(StudyPlannerService $planner): void
    {
        $this->planner = $planner;
    }

    public function messages(): array
    {
        return [
            'planForm.selected_disciplines.required' => __('planner.validation.select_discipline'),
            'planForm.selected_disciplines.min' => __('planner.validation.select_discipline'),
        ];
    }

    public function mount(): void
    {
        $this->todayTasks = collect();
        $this->upcomingTasks = collect();

        $this->loadPlan();
        $this->refreshTasks(generate: true);
    }

    public function savePlan(): void
    {
        $this->sanitizeSelectedDisciplines();
        $this->planForm['daily_sessions_target'] = 0;

        $this->validate($this->rules());

        $plan = $this->planner->syncPlan(
            auth()->user(),
            (int) $this->planForm['study_days_per_week'],
            (int) $this->planForm['daily_sessions_target'],
            $this->sessionsPayload(),
        );

        $this->loadPlan($plan);
        $this->refreshTasks();

        session()->flash('status', __('planner.messages.plan_saved'));
    }

    public function regenerateTasks(): void
    {
        $this->refreshTasks();

        session()->flash('status', __('planner.messages.tasks_refreshed'));
    }

    public function confirmReset(): void
    {
        $this->showResetConfirm = true;
    }

    public function resetPlanTasks(): void
    {
        $user = auth()->user();

        StudyPlanTask::query()
            ->withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->delete();

        if ($plan = $this->planner->findPlan($user)) {
            $plan->disciplines()->delete();
            $plan->delete();
        }

        $this->planForm['study_days_per_week'] = 5;
        $this->planForm['daily_sessions_target'] = 0;
        $this->planForm['selected_disciplines'] = [];
        $this->planForm['sessions_per_week'] = [];

        // Clear local collections to avoid flashing stale tasks.
        $this->todayTasks = collect();
        $this->upcomingTasks = collect();
        $this->planSummary['today_pending'] = 0;
        $this->planSummary['today_completed'] = 0;
        $this->planSummary['disciplines'] = 0;
        $this->planSummary['weekly_sessions'] = 0;

        $this->showResetConfirm = false;

        session()->flash('status', __('planner.messages.tasks_refreshed'));
    }

    public function markTaskCompleted(int $taskId): void
    {
        $task = StudyPlanTask::query()->findOrFail($taskId);
        $task->markCompleted();

        $this->refreshTasks();
    }

    public function reopenTask(int $taskId): void
    {
        $task = StudyPlanTask::query()->findOrFail($taskId);
        $task->reopen();

        $this->refreshTasks();
    }

    public function cancelTask(int $taskId): void
    {
        $task = StudyPlanTask::query()->findOrFail($taskId);
        $task->cancel();

        $this->refreshTasks();
    }

    public function updatedPlanFormSelectedDisciplines(): void
    {
        $this->sanitizeSelectedDisciplines();
    }

    public function updatedPlanFormStudyDaysPerWeek(): void
    {
        $this->updateUpcomingHorizon();
    }

    public function render(): View
    {
        return view('livewire.study.planner', [
            'availableDisciplines' => $this->availableDisciplines,
            'todayDate' => $this->todayDate(),
            'groupedUpcomingTasks' => $this->groupedUpcomingTasks(),
        ])->layout('layouts.app', [
            'title' => __('planner.title'),
        ]);
    }

    protected function loadPlan(?StudyPlan $plan = null): void
    {
        $plan ??= $this->planner
            ->findPlan(auth()->user())
            ?->load('disciplines');

        if (! $plan) {
            $this->planSummary = [
                'disciplines' => 0,
                'weekly_sessions' => 0,
                'today_pending' => 0,
                'today_completed' => 0,
            ];

            $this->planForm['study_days_per_week'] = 5;
            $this->planForm['daily_sessions_target'] = 0;
            $this->planForm['selected_disciplines'] = [];
            $this->planForm['sessions_per_week'] = [];

            return;
        }

        $this->planSummary['disciplines'] = $plan->disciplines->count();
        $this->planSummary['weekly_sessions'] = $plan->disciplines->count() * $plan->study_days_per_week;

        $this->planForm['study_days_per_week'] = $plan->study_days_per_week;
        $this->planForm['daily_sessions_target'] = $plan->daily_sessions_target;
        $this->planForm['selected_disciplines'] = $plan->disciplines
            ->pluck('discipline_id')
            ->map(fn (int $id) => (string) $id)
            ->values()
            ->all();

        $this->planForm['sessions_per_week'] = $plan->disciplines
            ->mapWithKeys(fn ($entry) => [(string) $entry->discipline_id => $entry->sessions_per_week])
            ->toArray();

        $this->updateUpcomingHorizon();
        $this->planSummary['weekly_sessions'] = $this->planSummary['disciplines'] * $this->planForm['study_days_per_week'];
    }

    protected function refreshTasks(bool $generate = true): void
    {
        $user = auth()->user();
        $today = $this->todayDate();

        if ($generate) {
            // Generate today's list and bring any overdue tasks forward.
            $todaySet = $this->planner->ensureDailyTasks($user, $today);
            $backlog = $this->planner->backlogTasks($user, $today);
            $this->todayTasks = $backlog->merge($todaySet);

            // Pre-schedule the next days so the UI can show a short horizon.
            $upcoming = collect();

            for ($i = 1; $i <= $this->upcomingDays; $i++) {
                $upcomingDate = $today->copy()->addDays($i);
                $upcoming = $upcoming->concat($this->planner->ensureDailyTasks($user, $upcomingDate));
            }

            $this->upcomingTasks = $upcoming;
        }

        // Always reload from the database to keep counters in sync.
        $todayBaseQuery = StudyPlanTask::query()
            ->ownedBy($user)
            ->whereDate('scheduled_for', '<=', $today);

        $this->todayTasks = (clone $todayBaseQuery)
            ->with('discipline')
            ->orderBy('scheduled_for')
            ->orderBy('id')
            ->get();

        $this->upcomingTasks = StudyPlanTask::query()
            ->ownedBy($user)
            ->with('discipline')
            ->where('status', StudyPlanTask::STATUS_PENDING)
            ->whereDate('scheduled_for', '>', $today)
            ->orderBy('scheduled_for')
            ->orderBy('id')
            ->get();

        $this->planSummary['today_pending'] = (clone $todayBaseQuery)
            ->where('status', StudyPlanTask::STATUS_PENDING)
            ->count();

        $this->planSummary['today_completed'] = (clone $todayBaseQuery)
            ->where('status', StudyPlanTask::STATUS_COMPLETED)
            ->whereDate('scheduled_for', $today)
            ->count();
    }

    protected function updateUpcomingHorizon(): void
    {
        $days = (int) ($this->planForm['study_days_per_week'] ?? 0);
        $this->upcomingDays = max(0, $days - 1);
    }

    protected function sanitizeSelectedDisciplines(): void
    {
        $selected = collect($this->planForm['selected_disciplines'] ?? [])
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        $this->planForm['selected_disciplines'] = $selected;

        $sessions = $this->planForm['sessions_per_week'] ?? [];

        foreach ($selected as $disciplineId) {
            if (! array_key_exists($disciplineId, $sessions)) {
                $sessions[$disciplineId] = 3;
            }
        }

        foreach ($sessions as $disciplineId => $value) {
            if (! in_array($disciplineId, $selected, true)) {
                unset($sessions[$disciplineId]);
            }
        }

        $this->planForm['sessions_per_week'] = $sessions;
    }

    protected function sessionsPayload(): array
    {
        $sessions = [];

        foreach ($this->planForm['selected_disciplines'] as $disciplineId) {
            $sessions[(int) $disciplineId] = (int) ($this->planForm['sessions_per_week'][$disciplineId] ?? 1);
        }

        return $sessions;
    }

    protected function todayDate(): Carbon
    {
        return now()->startOfDay();
    }

    protected function groupedUpcomingTasks(): Collection
    {
        return $this->upcomingTasks
            ->groupBy(fn (StudyPlanTask $task) => $task->scheduled_for?->format('Y-m-d'))
            ->sortKeys();
    }

    protected function rules(): array
    {
        return [
            'planForm.study_days_per_week' => ['required', Rule::in([3, 5, 7])],
            'planForm.selected_disciplines' => ['required', 'array', 'min:1'],
            'planForm.selected_disciplines.*' => [
                'integer',
                Rule::exists('disciplines', 'id')->where(fn ($query) => $query->where('user_id', auth()->id())),
            ],
            'planForm.sessions_per_week.*' => ['nullable', 'integer', 'min:1', 'max:21'],
        ];
    }

    public function getAvailableDisciplinesProperty(): Collection
    {
        return auth()->user()
            ->disciplines()
            ->select('disciplines.id', 'disciplines.title')
            ->whereHas('notes', fn ($query) => $query->where('is_flashcard', true))
            ->orderBy('title')
            ->get();
    }
}
