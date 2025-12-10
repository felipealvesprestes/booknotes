<?php

namespace App\Livewire\Study;

use App\Models\Discipline;
use App\Models\Note;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class Exercises extends Component
{
    public ?int $disciplineId = null;

    public string $mode = 'true_false';

    public ?array $exercise = null;

    public ?bool $answeredCorrectly = null;

    public ?string $feedbackTitle = null;

    public ?string $feedbackBody = null;

    public array $stats = [
        'answered' => 0,
        'correct' => 0,
    ];

    public array $fillGuesses = [];

    public ?string $warningMessage = null;

    protected array $modes = [
        'true_false',
        'fill_blank',
        'multiple_choice',
    ];

    protected $queryString = [
        'disciplineId' => ['except' => null],
        'mode' => ['except' => 'true_false'],
    ];

    public function mount(?int $disciplineId = null): void
    {
        if ($disciplineId) {
            $this->disciplineId = $disciplineId;
        }

        $this->ensureValidMode();
        $this->loadExercise();
    }

    public function updatedDisciplineId(): void
    {
        $this->disciplineId = $this->disciplineId ?: null;
        $this->loadExercise();
    }

    public function switchMode(string $mode): void
    {
        if (! in_array($mode, $this->modes, true)) {
            return;
        }

        if ($this->mode === $mode) {
            return;
        }

        $this->mode = $mode;
        $this->resetStats();
        $this->loadExercise();
    }

    public function submitTrueFalse(bool $guess): void
    {
        if ($this->mode !== 'true_false' || empty($this->exercise)) {
            return;
        }

        $isCorrect = ($this->exercise['statement_is_true'] ?? false) === $guess;

        $this->registerResult($isCorrect);
    }

    public function submitFillBlank(): void
    {
        if ($this->mode !== 'fill_blank' || empty($this->exercise)) {
            return;
        }

        $blanks = $this->exercise['blanks'] ?? [];

        if (empty($blanks)) {
            return;
        }

        $isCorrect = true;

        foreach ($blanks as $index => $blank) {
            $expected = Str::lower(trim($blank['answer'] ?? ''));
            $guess = Str::lower(trim($this->fillGuesses[$index] ?? ''));

            if ($expected === '') {
                continue;
            }

            if ($guess === '') {
                $this->feedbackTitle = __('Type your guess before checking the answer.');
                $this->feedbackBody = null;

                return;
            }

            if ($guess !== $expected) {
                $isCorrect = false;
            }
        }

        $this->registerResult($isCorrect);
    }

    public function submitMultipleChoice(string $key): void
    {
        if ($this->mode !== 'multiple_choice' || empty($this->exercise)) {
            return;
        }

        $isCorrect = $key === ($this->exercise['correct_option_key'] ?? null);

        $this->registerResult($isCorrect);
    }

    public function nextExercise(): void
    {
        $this->loadExercise();
    }

    public function render(): View
    {
        $accuracy = $this->stats['answered'] > 0
            ? (int) round(($this->stats['correct'] / max(1, $this->stats['answered'])) * 100)
            : null;

        return view('livewire.study.exercises', [
            'flashcardCount' => $this->flashcards->count(),
            'accuracy' => $accuracy,
            'disciplines' => $this->disciplines,
        ])->layout('layouts.app', [
            'title' => __('Exercises'),
        ]);
    }

    protected function loadExercise(): void
    {
        $this->ensureValidMode();
        $this->fillGuesses = [];
        $this->answeredCorrectly = null;
        $this->feedbackTitle = null;
        $this->feedbackBody = null;
        $this->warningMessage = null;

        $cards = $this->flashcards;

        if ($cards->isEmpty()) {
            $this->exercise = null;
            $this->warningMessage = __('Create at least one flashcard to unlock the exercises.');

            return;
        }

        $exercise = match ($this->mode) {
            'fill_blank' => $this->generateFillBlankExercise($cards),
            'multiple_choice' => $this->generateMultipleChoiceExercise($cards),
            default => $this->generateTrueFalseExercise($cards),
        };

        if (! $exercise) {
            $this->exercise = null;
            $this->warningMessage = $this->warningForMode($this->mode);

            return;
        }

        $this->exercise = $exercise;

        if ($this->mode === 'fill_blank') {
            $blankCount = count($exercise['blanks'] ?? []);
            $this->fillGuesses = $blankCount > 0
                ? array_fill(0, $blankCount, '')
                : [];
        }
    }

    protected function ensureValidMode(): void
    {
        if (! in_array($this->mode, $this->modes, true)) {
            $this->mode = $this->modes[0];
        }
    }

    protected function registerResult(bool $isCorrect): void
    {
        $this->answeredCorrectly = $isCorrect;
        $this->stats['answered']++;

        if ($isCorrect) {
            $this->stats['correct']++;
        }

        $this->feedbackTitle = $isCorrect ? __('Great job!') : __('Keep going!');
        $this->feedbackBody = $isCorrect
            ? __('You matched the correct answer.')
            : __('Review the correct answer below and try again.');
    }

    protected function resetStats(): void
    {
        $this->stats = [
            'answered' => 0,
            'correct' => 0,
        ];
    }

    protected function generateTrueFalseExercise(Collection $cards): ?array
    {
        $card = $cards->random();
        $question = $this->questionFor($card);
        $correctAnswer = $this->answerFor($card);

        if ($question === '' || $correctAnswer === '') {
            return null;
        }

        $shouldUseCorrect = $cards->count() === 1 ? true : (bool) random_int(0, 1);

        $statement = $correctAnswer;

        if (! $shouldUseCorrect) {
            $alternate = $cards
                ->where('id', '!=', $card->id)
                ->shuffle()
                ->first();

            if ($alternate) {
                $statement = $this->answerFor($alternate);
            } else {
                $statement = $correctAnswer;
            }
        }

        $isTrue = Str::lower($statement) === Str::lower($correctAnswer);

        return [
            'type' => 'true_false',
            'question' => $question,
            'statement' => $statement,
            'statement_is_true' => $isTrue,
            'correct_answer' => $correctAnswer,
        ];
    }

    protected function generateFillBlankExercise(Collection $cards): ?array
    {
        $card = $cards->random();
        $question = $this->questionFor($card);
        $answer = $this->answerFor($card);

        if ($answer === '') {
            return null;
        }

        $tokens = preg_split('/(\b[\p{L}\p{N}]+\b)/u', $answer, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($tokens === false || empty($tokens)) {
            return null;
        }

        $candidateIndices = [];

        foreach ($tokens as $index => $token) {
            $clean = preg_replace('/[^\p{L}\p{N}]/u', '', $token ?? '');

            if ($clean !== '' && mb_strlen($clean) >= 3) {
                $candidateIndices[] = $index;
            }
        }

        if (count($candidateIndices) < 4) {
            return null;
        }

        shuffle($candidateIndices);

        $selectedIndices = array_slice($candidateIndices, 0, 4);

        sort($selectedIndices);

        $selectedMap = [];
        $blanks = [];

        foreach ($selectedIndices as $order => $tokenIndex) {
            $selectedMap[$tokenIndex] = $order;
            $blanks[] = [
                'index' => $order,
                'answer' => trim($tokens[$tokenIndex]),
            ];
        }

        $segments = [];

        foreach ($tokens as $index => $token) {
            if ($token === null) {
                continue;
            }

            if (array_key_exists($index, $selectedMap)) {
                $segments[] = [
                    'type' => 'blank',
                    'index' => $selectedMap[$index],
                    'label' => $selectedMap[$index] + 1,
                ];
            } else {
                $segments[] = [
                    'type' => 'text',
                    'value' => $token,
                ];
            }
        }

        return [
            'type' => 'fill_blank',
            'question' => $question,
            'segments' => $segments,
            'blanks' => collect($blanks)
                ->map(fn (array $blank) => [
                    'index' => $blank['index'],
                    'label' => __('Blank :number', ['number' => $blank['index'] + 1]),
                    'answer' => $blank['answer'],
                ])
                ->values()
                ->all(),
            'correct_answer' => $answer,
        ];
    }

    protected function generateMultipleChoiceExercise(Collection $cards): ?array
    {
        $card = $cards->random();
        $question = $this->questionFor($card);
        $answer = $this->answerFor($card);

        if ($answer === '') {
            return null;
        }

        $options = collect([$answer]);

        $cards
            ->where('id', '!=', $card->id)
            ->shuffle()
            ->each(function ($candidate) use (&$options) {
                if ($options->count() >= 4) {
                    return false;
                }

                $text = $this->answerFor($candidate);

                if ($text !== '' && ! $options->contains($text)) {
                    $options->push($text);
                }

                return null;
            });

        if ($options->count() < 2) {
            return null;
        }

        $shuffled = $options->shuffle()->values();

        $letters = ['A', 'B', 'C', 'D'];

        $prepared = $shuffled->map(function (string $text, int $index) use ($answer, $letters) {
            return [
                'key' => $letters[$index] ?? chr(65 + $index),
                'text' => $text,
                'is_correct' => Str::lower($text) === Str::lower($answer),
            ];
        });

        $correct = $prepared->firstWhere('is_correct', true);

        if (! $correct) {
            return null;
        }

        return [
            'type' => 'multiple_choice',
            'question' => $question,
            'options' => $prepared
                ->map(fn ($option) => [
                    'key' => $option['key'],
                    'text' => $option['text'],
                ])
                ->all(),
            'correct_option_key' => $correct['key'],
            'correct_answer' => $answer,
        ];
    }

    protected function questionFor(Note $note): string
    {
        return trim($note->flashcard_question ?: $note->title ?: '');
    }

    protected function answerFor(Note $note): string
    {
        $content = $note->flashcard_answer ?: strip_tags((string) $note->content) ?: $note->title ?: '';

        return trim(preg_replace('/\s+/u', ' ', $content) ?? '');
    }

    protected function warningForMode(string $mode): string
    {
        return match ($mode) {
            'fill_blank' => __('We could not find at least four meaningful words to hide. Add richer flashcards or try another mode.'),
            'multiple_choice' => __('Add at least four flashcard answers to unlock multiple choice exercises.'),
            default => __('Not enough flashcards to create exercises right now.'),
        };
    }

    public function getFlashcardsProperty(): Collection
    {
        return Note::query()
            ->where('is_flashcard', true)
            ->when($this->disciplineId, fn ($query) => $query->where('discipline_id', $this->disciplineId))
            ->orderByDesc('updated_at')
            ->get();
    }

    public function getDisciplinesProperty(): Collection
    {
        return Discipline::query()
            ->orderBy('title')
            ->get();
    }
}
