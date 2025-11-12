<?php

namespace App\Livewire\Study;

use App\Models\Discipline;
use App\Models\FlashcardSession;
use App\Models\Log;
use App\Models\Note;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Flashcards extends Component
{
    use WithPagination;

    public ?int $sessionId = null;

    public ?int $disciplineFilter = null;

    public bool $showAnswer = false;

    public int $todaySessionsPerPage = 5;

    protected string $todaySessionsPageName = 'todaySessionsPage';

    public bool $focusMode = false;

    public function mount(): void
    {
        $activeSession = $this->getActiveSession();

        if ($activeSession) {
            $this->sessionId = $activeSession->id;
            $this->disciplineFilter = $activeSession->discipline_id;
        }
    }

    public function startSession(): void
    {
        $disciplineId = $this->disciplineFilter ?: null;

        $existingSession = $this->getActiveSession($disciplineId);

        if ($existingSession) {
            $this->sessionId = $existingSession->id;
            $this->showAnswer = false;

            session()->flash('status', __('Resuming your active study session.'));

            return;
        }

        $notesQuery = Note::query()
            ->where('is_flashcard', true)
            ->when($disciplineId, fn ($query) => $query->where('discipline_id', $disciplineId));

        $noteIds = $notesQuery
            ->orderByDesc('updated_at')
            ->pluck('id')
            ->shuffle()
            ->values()
            ->all();

        if (empty($noteIds)) {
            $this->sessionId = null;
            $this->showAnswer = false;
            $this->focusMode = false;

            $this->addError('session', __('No flashcards available for this filter. Create flashcards to start studying.'));

            return;
        }

        $session = FlashcardSession::create([
            'status' => 'active',
            'total_cards' => count($noteIds),
            'current_index' => 0,
            'correct_count' => 0,
            'incorrect_count' => 0,
            'accuracy' => 0,
            'note_ids' => $noteIds,
            'studied_at' => now(),
            'discipline_id' => $disciplineId,
        ]);

        $this->sessionId = $session->id;
        $this->showAnswer = false;

        Log::create([
            'action' => 'flashcard.session_started',
            'context' => [
                'session_id' => $session->id,
                'discipline_id' => $disciplineId,
                'total_cards' => $session->total_cards,
            ],
        ]);

        session()->flash('status', __('New study session created. Good studies!'));
    }

    public function updatedTodaySessionsPerPage(): void
    {
        $this->resetPage(pageName: $this->todaySessionsPageName);
    }

    public function markCorrect(): void
    {
        $this->recordAnswer(true);
    }

    public function markIncorrect(): void
    {
        $this->recordAnswer(false);
    }

    public function revealAnswer(): void
    {
        $this->showAnswer = true;
    }

    public function resumeSession(int $sessionId): void
    {
        $session = FlashcardSession::query()->find($sessionId);

        if (! $session) {
            $this->addError('session', __('Unable to find that session.'));

            return;
        }

        $this->sessionId = $session->id;
        $this->disciplineFilter = $session->discipline_id;
        $this->showAnswer = false;

        session()->flash('status', __('Session loaded.'));
    }

    public function restartSession(): void
    {
        if (! $this->disciplineFilter && ! $this->sessionId) {
            $this->addError('session', __('Select a discipline or start a session first.'));

            return;
        }

        $this->resetActiveSession();
        $this->startSession();
    }

    public function toggleFocusMode(): void
    {
        if ($this->focusMode) {
            $this->focusMode = false;

            return;
        }

        $session = $this->session;

        if (! $session || ! $session->hasPendingCards()) {
            $this->focusMode = false;
            $this->addError('session', __('Start a session before entering focus mode.'));

            return;
        }

        $this->focusMode = true;
    }

    public function getSessionProperty(): ?FlashcardSession
    {
        return $this->sessionId
            ? FlashcardSession::query()->with('discipline')->find($this->sessionId)
            : null;
    }

    public function getCurrentCardProperty(): ?Note
    {
        $session = $this->session;

        if (! $session?->hasPendingCards()) {
            return null;
        }

        $noteId = $session->currentNoteId();

        return $noteId
            ? Note::query()->find($noteId)
            : null;
    }

    public function getProgressPercentProperty(): int
    {
        $session = $this->session;

        if (! $session || empty($session->note_ids)) {
            return 0;
        }

        $totalQueue = max(1, count($session->note_ids));

        return (int) min(100, round(($session->current_index / $totalQueue) * 100));
    }

    public function getTodaySessionsProperty()
    {
        return $this->todaySessionsQuery()
            ->paginate($this->todaySessionsPerPage, ['*'], $this->todaySessionsPageName);
    }

    public function getTodaySessionsCollectionProperty(): Collection
    {
        return $this->todaySessionsQuery()->get();
    }

    public function getDailyHistoryProperty(): Collection
    {
        return FlashcardSession::query()
            ->with('discipline')
            ->where('studied_at', '>=', now()->subDays(30))
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
                    'sessions' => $sessions,
                ];
            })
            ->sortKeysDesc()
            ->values();
    }

    public function getDisciplinesProperty(): Collection
    {
        return Discipline::query()
            ->orderBy('title')
            ->get();
    }

    public function getTodaySummaryProperty(): array
    {
        $sessions = $this->todaySessionsCollection;

        $correct = $sessions->sum('correct_count');
        $incorrect = $sessions->sum('incorrect_count');
        $reviewed = $correct + $incorrect;

        return [
            'reviewed' => $reviewed,
            'correct' => $correct,
            'incorrect' => $incorrect,
            'accuracy' => $reviewed > 0 ? (int) round(($correct / $reviewed) * 100) : 0,
        ];
    }

    public function render(): View
    {
        return view('livewire.study.flashcards', [
            'session' => $this->session,
            'currentCard' => $this->currentCard,
            'todaySummary' => $this->todaySummary,
            'todaySessions' => $this->todaySessions,
            'dailyHistory' => $this->dailyHistory,
            'disciplines' => $this->disciplines,
            'progressPercent' => $this->progressPercent,
        ])->layout('layouts.app', [
            'title' => __('Flashcards'),
        ]);
    }

    protected function recordAnswer(bool $isCorrect): void
    {
        $session = $this->session;

        if (! $session) {
            $this->addError('session', __('Start a session before answering cards.'));

            return;
        }

        if (! $session->hasPendingCards()) {
            session()->flash('status', __('All cards reviewed for this session. Create a new session to keep studying.'));

            return;
        }

        $noteId = $session->currentNoteId();
        $note = $noteId ? Note::query()->find($noteId) : null;

        $session->recordAnswer($isCorrect);

        $this->showAnswer = false;

        Log::create([
            'action' => 'flashcard.answered',
            'context' => [
                'session_id' => $session->id,
                'note_id' => $note?->id,
                'result' => $isCorrect ? 'correct' : 'incorrect',
            ],
        ]);

        if ($session->status === 'completed') {
            session()->flash('status', __('Session completed! Review your stats below.'));
            $this->focusMode = false;
        }
    }

    protected function resetActiveSession(): void
    {
        if (! $this->sessionId) {
            return;
        }

        $session = FlashcardSession::query()->find($this->sessionId);

        if (! $session) {
            return;
        }

        $session->update(['status' => 'archived']);
    }

    protected function getActiveSession(?int $disciplineId = null): ?FlashcardSession
    {
        return FlashcardSession::query()
            ->active()
            ->when($disciplineId, fn ($query) => $query->where('discipline_id', $disciplineId))
            ->whereDate('studied_at', now()->toDateString())
            ->latest('updated_at')
            ->first();
    }

    protected function todaySessionsQuery()
    {
        return FlashcardSession::query()
            ->with('discipline')
            ->whereDate('studied_at', now()->toDateString())
            ->orderByDesc('studied_at');
    }
}
