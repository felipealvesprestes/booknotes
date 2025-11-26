<?php

namespace App\Livewire\Study;

use App\Models\Discipline;
use App\Models\Log;
use App\Models\Notebook;
use App\Models\Note;
use App\Models\SimulatedExam as SimulatedExamModel;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class SimulatedExam extends Component
{
    public string $scopeType = 'notebook';

    public ?int $notebookId = null;

    public ?int $disciplineId = null;

    public int $questionCount = 10;

    public bool $timerHidden = false;

    public array $questions = [];

    public int $answeredCount = 0;

    public int $correctCount = 0;

    public bool $examStarted = false;

    public bool $examFinished = false;

    public ?string $startedAt = null;

    public ?string $completedAt = null;

    public ?int $durationSeconds = null;

    public ?array $activeScope = null;

    protected array $questionCountOptions = [10, 30, 50];

    public function updatedScopeType(): void
    {
        $this->notebookId = null;
        $this->disciplineId = null;
    }

    public function startExam(): void
    {
        $this->resetErrorBag();

        $this->validate($this->rules());

        $this->questionCount = (int) $this->questionCount;

        $this->resetExamState();

        $cards = $this->flashcardsForScope();

        if ($cards->isEmpty()) {
            $this->addError('exam', __('Add flashcards to this selection to unlock the simulated test.'));

            return;
        }

        if ($cards->count() < $this->questionCount) {
            $this->addError('exam', __('You need at least :count flashcards for this selection.', ['count' => $this->questionCount]));

            return;
        }

        $uniqueAnswers = $cards
            ->map(fn (Note $note) => $this->answerFor($note))
            ->filter()
            ->unique(fn (string $answer) => Str::lower($answer));

        if ($uniqueAnswers->count() < 4) {
            $this->addError('exam', __('Add more detailed flashcard answers to unlock multiple choice questions.'));

            return;
        }

        $questions = $this->generateQuestionSet($cards);

        if (count($questions) < $this->questionCount) {
            $this->addError('exam', __('We could not prepare enough valid questions. Update your flashcards and try again.'));

            return;
        }

        $this->questions = $questions;
        $this->examStarted = true;
        $this->examFinished = false;
        $this->startedAt = now()->toIso8601String();
        $this->activeScope = $this->scopeSnapshot();
    }

    public function selectOption(int $questionIndex, string $key): void
    {
        if (! $this->examStarted || $this->examFinished) {
            return;
        }

        $question = $this->questions[$questionIndex] ?? null;

        if (! $question) {
            return;
        }

        $optionExists = collect($question['options'] ?? [])
            ->contains(fn (array $option) => ($option['key'] ?? null) === $key);

        if (! $optionExists) {
            return;
        }

        $this->questions[$questionIndex]['selected_key'] = $key;
    }


    public function finishExam(): void
    {
        if (! $this->examStarted || $this->examFinished) {
            return;
        }

        $totalQuestions = $this->totalQuestions;

        $answered = 0;
        $correct = 0;

        foreach ($this->questions as $index => $question) {
            $selected = $question['selected_key'] ?? null;
            $correctKey = $question['correct_key'] ?? null;

            if ($selected !== null) {
                $answered++;
            }

            $isCorrect = $selected !== null && $selected === $correctKey;

            $this->questions[$index]['is_correct'] = $selected === null ? null : $isCorrect;
        }

        $correct = collect($this->questions)
            ->filter(fn (array $question) => $question['is_correct'] === true)
            ->count();

        $this->answeredCount = $answered;
        $this->correctCount = $correct;

        $this->examFinished = true;
        $this->completedAt = now()->toIso8601String();
        $this->durationSeconds = $this->calculateDurationSeconds();

        $this->persistAttempt();
    }

    public function toggleTimerVisibility(): void
    {
        $this->timerHidden = ! $this->timerHidden;
    }

    public function render(): View
    {
        return view('livewire.study.simulated-exam', [
            'notebooks' => $this->notebooks,
            'disciplines' => $this->disciplines,
            'recentAttempts' => $this->recentAttempts,
            'progressPercent' => $this->progressPercent,
            'totalQuestions' => $this->totalQuestions,
            'score' => $this->score,
            'availableFlashcards' => $this->availableFlashcards,
            'answeredSelections' => $this->answeredSelections,
        ])->layout('layouts.app', [
            'title' => __('Simulated test'),
        ]);
    }

    protected function rules(): array
    {
        $userId = Auth::id() ?? 0;

        return [
            'scopeType' => ['required', Rule::in(['notebook', 'discipline'])],
            'questionCount' => ['required', Rule::in($this->questionCountOptions)],
            'notebookId' => [
                'nullable',
                Rule::requiredIf(fn () => $this->scopeType === 'notebook'),
                'integer',
                Rule::exists('notebooks', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'disciplineId' => [
                'nullable',
                Rule::requiredIf(fn () => $this->scopeType === 'discipline'),
                'integer',
                Rule::exists('disciplines', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
        ];
    }

    protected function resetExamState(): void
    {
        $this->questions = [];
        $this->answeredCount = 0;
        $this->correctCount = 0;
        $this->examStarted = false;
        $this->examFinished = false;
        $this->startedAt = null;
        $this->completedAt = null;
        $this->durationSeconds = null;
        $this->activeScope = null;
    }

    protected function flashcardsForScope(): Collection
    {
        $query = Note::query()
            ->with(['discipline.notebook'])
            ->where('is_flashcard', true);

        if ($this->scopeType === 'discipline' && $this->disciplineId) {
            $query->where('discipline_id', $this->disciplineId);
        } elseif ($this->scopeType === 'notebook' && $this->notebookId) {
            $disciplineIds = Discipline::query()
                ->where('notebook_id', $this->notebookId)
                ->pluck('id');

            if ($disciplineIds->isEmpty()) {
                return collect();
            }

            $query->whereIn('discipline_id', $disciplineIds);
        }

        return $query->get();
    }

    protected function generateQuestionSet(Collection $cards): array
    {
        $questions = [];
        $shuffled = $cards->shuffle()->values();

        foreach ($shuffled as $index => $card) {
            if (count($questions) >= $this->questionCount) {
                break;
            }

            $question = $this->buildQuestion($card, $cards, $index + 1);

            if ($question) {
                $questions[] = $question;
            }
        }

        return $questions;
    }

    protected function buildQuestion(Note $note, Collection $pool, int $order): ?array
    {
        $questionText = $this->questionFor($note);
        $answerText = $this->answerFor($note);

        if ($questionText === '' || $answerText === '') {
            return null;
        }

        $distractors = $pool
            ->where('id', '!=', $note->id)
            ->map(fn (Note $candidate) => $this->answerFor($candidate))
            ->filter(fn (string $text) => $text !== '' && Str::lower($text) !== Str::lower($answerText))
            ->unique(fn (string $text) => Str::lower($text))
            ->shuffle()
            ->take(3)
            ->values();

        if ($distractors->count() < 3) {
            return null;
        }

        $options = collect([$answerText])
            ->merge($distractors)
            ->shuffle()
            ->values();

        $letters = ['A', 'B', 'C', 'D'];

        $prepared = $options->map(function (string $text, int $index) use ($letters) {
            return [
                'key' => $letters[$index] ?? chr(65 + $index),
                'text' => $text,
            ];
        });

        $correct = $prepared->first(function (array $option) use ($answerText) {
            return Str::lower($option['text']) === Str::lower($answerText);
        });

        if (! $correct) {
            return null;
        }

        return [
            'order' => $order,
            'note_id' => $note->id,
            'question' => $questionText,
            'answer' => $answerText,
            'options' => $prepared->all(),
            'correct_key' => $correct['key'],
            'selected_key' => null,
            'is_correct' => null,
            'discipline_title' => $note->discipline?->title,
            'notebook_title' => $note->discipline?->notebook?->title,
        ];
    }

    protected function questionFor(Note $note): string
    {
        return trim($note->flashcard_question ?: $note->title ?: '');
    }

    protected function answerFor(Note $note): string
    {
        $content = $note->flashcard_answer ?: strip_tags((string) $note->content) ?: $note->title ?: '';

        $normalized = preg_replace('/\s+/u', ' ', $content);

        return trim($normalized ?? '');
    }

    protected function scopeSnapshot(): ?array
    {
        if ($this->scopeType === 'discipline' && $this->disciplineId) {
            $discipline = Discipline::query()->with('notebook')->find($this->disciplineId);

            if (! $discipline) {
                return null;
            }

            return [
                'type' => 'discipline',
                'label' => $discipline->title,
                'sub_label' => $discipline->notebook?->title,
                'notebook_id' => $discipline->notebook_id,
                'discipline_id' => $discipline->id,
            ];
        }

        if ($this->scopeType === 'notebook' && $this->notebookId) {
            $notebook = Notebook::query()->find($this->notebookId);

            if (! $notebook) {
                return null;
            }

            return [
                'type' => 'notebook',
                'label' => $notebook->title,
                'sub_label' => null,
                'notebook_id' => $notebook->id,
                'discipline_id' => null,
            ];
        }

        return null;
    }

    protected function calculateDurationSeconds(): int
    {
        $start = $this->startedAt ? Carbon::parse($this->startedAt) : now();
        $end = $this->completedAt ? Carbon::parse($this->completedAt) : now();

        return max(0, $start->diffInSeconds($end));
    }

    protected function persistAttempt(): void
    {
        $total = $this->totalQuestions;
        $incorrect = max(0, $total - $this->correctCount);
        $score = $this->score;
        $startedAt = $this->startedAt ? Carbon::parse($this->startedAt) : now();
        $completedAt = $this->completedAt ? Carbon::parse($this->completedAt) : now();

        $attempt = SimulatedExamModel::create([
            'scope_type' => $this->activeScope['type'] ?? $this->scopeType,
            'notebook_id' => $this->activeScope['notebook_id'] ?? null,
            'discipline_id' => $this->activeScope['discipline_id'] ?? null,
            'question_count' => $total,
            'answered_count' => $this->answeredCount,
            'correct_count' => $this->correctCount,
            'incorrect_count' => $incorrect,
            'score' => $score,
            'duration_seconds' => $this->durationSeconds ?? 0,
            'status' => 'completed',
            'questions' => $this->questions,
            'metadata' => [
                'scope_label' => $this->activeScope['label'] ?? null,
                'scope_sub_label' => $this->activeScope['sub_label'] ?? null,
                'question_goal' => $this->questionCount,
            ],
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
        ]);

        Log::create([
            'action' => 'simulated.exam_completed',
            'context' => [
                'exam_id' => $attempt->id,
                'score' => $score,
                'question_count' => $total,
                'duration_seconds' => $this->durationSeconds ?? 0,
            ],
        ]);
    }

    public function getNotebooksProperty(): Collection
    {
        return Notebook::query()
            ->orderBy('title')
            ->get();
    }

    public function getDisciplinesProperty(): Collection
    {
        return Discipline::query()
            ->with('notebook')
            ->orderBy('title')
            ->get();
    }

    public function getRecentAttemptsProperty(): Collection
    {
        return SimulatedExamModel::query()
            ->with(['discipline', 'notebook'])
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();
    }

    public function getAnsweredSelectionsProperty(): int
    {
        return collect($this->questions)
            ->filter(fn (array $question) => ($question['selected_key'] ?? null) !== null)
            ->count();
    }

    public function getProgressPercentProperty(): int
    {
        if ($this->totalQuestions === 0) {
            return 0;
        }

        $answered = $this->examFinished ? $this->answeredCount : $this->answeredSelections;

        return (int) min(100, round(($answered / $this->totalQuestions) * 100));
    }

    public function getTotalQuestionsProperty(): int
    {
        return count($this->questions);
    }

    public function getScoreProperty(): int
    {
        if ($this->totalQuestions === 0) {
            return 0;
        }

        return (int) round(($this->correctCount / max(1, $this->totalQuestions)) * 100);
    }

    public function getAvailableFlashcardsProperty(): int
    {
        if ($this->scopeType === 'discipline' && $this->disciplineId) {
            return Note::query()
                ->where('is_flashcard', true)
                ->where('discipline_id', $this->disciplineId)
                ->count();
        }

        if ($this->scopeType === 'notebook' && $this->notebookId) {
            $disciplineIds = Discipline::query()
                ->where('notebook_id', $this->notebookId)
                ->pluck('id');

            if ($disciplineIds->isEmpty()) {
                return 0;
            }

            return Note::query()
                ->where('is_flashcard', true)
                ->whereIn('discipline_id', $disciplineIds)
                ->count();
        }

        return 0;
    }
}
