<?php

namespace App\Livewire\Study;

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

    public string $fillGuess = '';

    public ?string $warningMessage = null;

    protected array $modes = [
        'true_false',
        'fill_blank',
        'multiple_choice',
    ];

    public function mount(?int $disciplineId = null): void
    {
        $this->disciplineId = $disciplineId;
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

        $expected = Str::lower(trim($this->exercise['missing_word'] ?? ''));
        $guess = Str::lower(trim($this->fillGuess));

        if ($expected === '') {
            return;
        }

        if ($guess === '') {
            $this->feedbackTitle = __('Type your guess before checking the answer.');
            $this->feedbackBody = null;

            return;
        }

        $this->registerResult($expected === $guess);
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
        ]);
    }

    protected function loadExercise(): void
    {
        $this->fillGuess = '';
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

        $words = collect(preg_split('/\s+/u', $answer, -1, PREG_SPLIT_NO_EMPTY));

        if ($words->isEmpty()) {
            return null;
        }

        $candidates = $words->filter(function (string $word) {
            $clean = preg_replace('/[^\p{L}\p{N}]/u', '', $word);

            return mb_strlen($clean ?? '') >= 3;
        });

        if ($candidates->isEmpty()) {
            $candidates = $words;
        }

        $target = $candidates->random();
        $missing = trim($target, " \t\n\r\0\x0B.,!?;:()[]{}\"'");

        if ($missing === '') {
            return null;
        }

        $blanked = Str::replaceFirst($target, '_____', $answer);

        if ($blanked === $answer) {
            $pattern = '/' . preg_quote($missing, '/') . '/iu';
            $blanked = preg_replace($pattern, '_____', $answer, 1) ?: $answer;
        }

        return [
            'type' => 'fill_blank',
            'question' => $question,
            'text_with_blank' => $blanked,
            'missing_word' => $missing,
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
            'fill_blank' => __('We could not find a long enough answer to hide a word. Add richer flashcards or try another mode.'),
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
}
