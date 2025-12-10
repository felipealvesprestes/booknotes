<?php

namespace App\Livewire\Dashboard;

use App\Models\Discipline;
use App\Models\FlashcardSession;
use App\Models\Note;
use App\Models\Notebook;
use App\Models\StudyPlanTask;
use App\Services\Planner\StudyPlannerService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class Overview extends Component
{
    /**
     * Controls whether the profile reminder modal should be visible.
     */
    public bool $showProfileCompletionModal = false;

    /**
     * @var list<string>
     */
    public array $missingProfileFields = [];

    public function mount(): void
    {
        $this->missingProfileFields = $this->resolveMissingProfileFields();
        $this->showProfileCompletionModal = $this->missingProfileFields !== [];
    }

    public function render(): View
    {
        $noteStats = Note::query()
            ->selectRaw('COUNT(*) as total_notes')
            ->selectRaw('SUM(CASE WHEN is_flashcard THEN 1 ELSE 0 END) as flashcards')
            ->first();

        $totalNotes = (int) ($noteStats?->total_notes ?? 0);
        $totalFlashcards = (int) ($noteStats?->flashcards ?? 0);

        $metrics = [
            'notebooks' => Notebook::query()->count(),
            'disciplines' => Discipline::query()->count(),
            'notes' => $totalNotes,
            'flashcards' => $totalFlashcards,
        ];

        $activeSessions = FlashcardSession::query()
            ->active()
            ->count();

        $completedSessions = FlashcardSession::query()
            ->where('status', 'completed')
            ->count();

        $recentSessions = FlashcardSession::query()
            ->with('discipline')
            ->orderByDesc('studied_at')
            ->limit(5)
            ->get();

        $recentSessions->each->ensureStatusFromProgress();

        $last30DaySessions = FlashcardSession::query()
            ->where('studied_at', '>=', now()->subDays(30))
            ->get();

        $correct30d = $last30DaySessions->sum('correct_count');
        $incorrect30d = $last30DaySessions->sum('incorrect_count');
        $reviewed30d = $correct30d + $incorrect30d;

        $accuracy30d = $reviewed30d > 0
            ? (int) round(($correct30d / max(1, $reviewed30d)) * 100)
            : 0;

        $notesByDiscipline = Discipline::query()
            ->with('notebook:id,title')
            ->withCount('notes')
            ->withCount(['notes as flashcards_count' => fn ($query) => $query->where('is_flashcard', true)])
            ->orderByDesc('notes_count')
            ->limit(5)
            ->get();

        $recentNotes = Note::query()
            ->with('discipline')
            ->latest()
            ->limit(5)
            ->get();

        $lastStudy = $recentSessions->first()?->studied_at;

        $dailyHistory = $this->buildDailyHistory();

        $planner = app(StudyPlannerService::class);
        $plannerTodayTasks = $planner->ensureDailyTasks(auth()->user(), now()->startOfDay());
        $plannerTodayPending = $plannerTodayTasks->where('status', StudyPlanTask::STATUS_PENDING);

        return view('livewire.dashboard.overview', [
            'metrics' => $metrics,
            'activeSessions' => $activeSessions,
            'completedSessions' => $completedSessions,
            'accuracy30d' => $accuracy30d,
            'reviewed30d' => $reviewed30d,
            'notesByDiscipline' => $notesByDiscipline,
            'recentNotes' => $recentNotes,
            'recentSessions' => $recentSessions,
            'lastStudy' => $lastStudy,
            'dailyHistory' => $dailyHistory,
            'plannerTodayTasks' => $plannerTodayTasks,
            'plannerTodayPending' => $plannerTodayPending,
        ])->layout('layouts.app', [
            'title' => __('Dashboard'),
        ]);
    }

    protected function buildDailyHistory(): Collection
    {
        return FlashcardSession::query()
            ->where('studied_at', '>=', now()->subDays(14))
            ->orderByDesc('studied_at')
            ->get()
            ->groupBy(fn (FlashcardSession $session) => $session->studied_at->toDateString())
            ->map(function (Collection $sessions, string $dateKey) {
                $correct = $sessions->sum('correct_count');
                $incorrect = $sessions->sum('incorrect_count');
                $reviewed = $correct + $incorrect;

                return [
                    'date' => Carbon::createFromFormat('Y-m-d', $dateKey),
                    'correct' => $correct,
                    'incorrect' => $incorrect,
                    'reviewed' => $reviewed,
                    'accuracy' => $reviewed > 0 ? (int) round(($correct / $reviewed) * 100) : 0,
                ];
            })
            ->sortByDesc('date')
            ->values();
    }

    /**
     * Determine which required profile fields are still missing.
     *
     * @return list<string>
     */
    protected function resolveMissingProfileFields(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $fields = [
            'cpf' => __('CPF'),
            'cep' => __('CEP'),
            'address_street' => __('Street'),
            'address_number' => __('Number'),
            'address_neighborhood' => __('Neighborhood'),
            'address_city' => __('City'),
            'address_state' => __('State'),
        ];

        return collect($fields)
            ->reject(fn (string $label, string $attribute): bool => filled($user->{$attribute}))
            ->values()
            ->all();
    }
}
