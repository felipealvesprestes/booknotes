<?php

namespace App\Livewire\Concerns;

use App\Exceptions\AiFlashcardGenerationException;
use App\Exceptions\AiFlashcardsLimitException;
use App\Models\Log;
use App\Models\Note;
use App\Services\Ai\AiFlashcardsUsageService;
use App\Services\Ai\GenerateFlashcardsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * @property \App\Models\Discipline $discipline
 */
trait HandlesAiFlashcardGenerator
{
    public bool $showAiFlashcardsModal = false;

    public string $aiGeneratorStep = 'form';

    public string $aiTopic = '';

    public ?string $aiDescription = null;

    public int $aiQuantity = 5;

    public bool $aiIsGenerating = false;

    public ?string $aiStatusMessage = null;

    public ?string $aiErrorMessage = null;

    public int $aiDailyLimit = 0;

    public int $aiUsedToday = 0;

    public int $aiRemainingToday = 0;

    public int $aiMinimumQuantity = 5;

    /** @var array<int, int> */
    public array $aiQuantityOptions = [];

    protected function bootAiFlashcardGenerator(): void
    {
        $this->aiQuantityOptions = $this->resolveAiQuantityOptions();
        $this->aiMinimumQuantity = $this->aiQuantityOptions[0] ?? 5;

        $this->reloadAiUsage(adjustQuantity: true);

        if (! in_array($this->aiQuantity, $this->aiQuantityOptions, true)) {
            $this->aiQuantity = $this->suggestAiQuantity();
        }
    }

    public function openAiFlashcardsModal(): void
    {
        $this->aiGeneratorStep = 'form';
        $this->aiStatusMessage = null;
        $this->aiErrorMessage = null;
        $this->resetErrorBag();

        $this->showAiFlashcardsModal = true;
    }

    public function closeAiFlashcardsModal(): void
    {
        $this->showAiFlashcardsModal = false;
        $this->aiGeneratorStep = 'form';
        $this->aiIsGenerating = false;
    }

    public function submitAiFlashcardsForm(): void
    {
        $this->resetAiMessages();

        $this->validate($this->aiGeneratorRules(), [], $this->aiGeneratorAttributes());

        $user = Auth::user();

        if (! $user) {
            return;
        }

        try {
            $this->aiUsageService()->ensureWithinLimit($user, $this->aiQuantity);
        } catch (AiFlashcardsLimitException $exception) {
            $this->handleAiLimitError($exception);

            return;
        }

        $this->aiGeneratorStep = 'confirm';
    }

    public function backToAiFlashcardsForm(): void
    {
        $this->aiGeneratorStep = 'form';
    }

    public function generateAiFlashcards(): void
    {
        $this->resetAiMessages();
        $this->aiIsGenerating = true;

        $this->validate($this->aiGeneratorRules(), [], $this->aiGeneratorAttributes());

        $user = Auth::user();

        if (! $user) {
            $this->aiIsGenerating = false;

            return;
        }

        try {
            $this->aiUsageService()->ensureWithinLimit($user, $this->aiQuantity);
        } catch (AiFlashcardsLimitException $exception) {
            $this->aiIsGenerating = false;
            $this->handleAiLimitError($exception);

            return;
        }

        try {
            $flashcards = $this->generatorService()->generate(
                $user,
                $this->discipline,
                $this->aiTopic,
                $this->aiDescription,
                $this->aiQuantity,
            );
        } catch (AiFlashcardGenerationException $exception) {
            $this->aiIsGenerating = false;
            $this->aiErrorMessage = $exception->getMessage();

            return;
        }

        if (empty($flashcards)) {
            $this->aiIsGenerating = false;
            $this->aiErrorMessage = __('ai_flashcards.no_response');

            return;
        }

        $created = $this->persistAiFlashcards($flashcards);

        if ($created === 0) {
            $this->aiIsGenerating = false;
            $this->aiErrorMessage = __('ai_flashcards.save_error');

            return;
        }

        $this->aiUsageService()->increment($user, $created);
        $this->reloadAiUsage(adjustQuantity: true);

        $this->aiStatusMessage = trans_choice(
            'ai_flashcards.generated',
            $created,
            [
                'count' => $created,
                'discipline' => $this->discipline->title,
            ],
        );

        $this->aiGeneratorStep = 'form';
        $this->aiIsGenerating = false;
    }

    protected function reloadAiUsage(bool $adjustQuantity = false): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $usageService = $this->aiUsageService();
        $usage = $usageService->getUsageForToday($user);

        $this->aiDailyLimit = $usageService->getDailyLimit();
        $this->aiUsedToday = (int) $usage->generated_count;
        $this->aiRemainingToday = max(0, $this->aiDailyLimit - $this->aiUsedToday);

        if ($adjustQuantity) {
            $this->aiQuantity = $this->suggestAiQuantity();
        }
    }

    protected function suggestAiQuantity(): int
    {
        $remaining = $this->aiRemainingToday;
        $options = $this->aiQuantityOptions;

        if (empty($options)) {
            return 5;
        }

        $minimum = $options[0];

        if ($remaining <= 0) {
            return $minimum;
        }

        if ($remaining >= $minimum) {
            return $minimum;
        }

        foreach ($options as $option) {
            if ($remaining >= $option) {
                return $option;
            }
        }

        return $minimum;
    }

    protected function handleAiLimitError(AiFlashcardsLimitException $exception): void
    {
        $remaining = $exception->remaining();

        $message = $remaining > 0
            ? trans_choice(
                'ai_flashcards.limit_remaining',
                $remaining,
                ['count' => $remaining],
            )
            : __('ai_flashcards.limit_reached');

        $this->addError('aiQuantity', $message);
        $this->reloadAiUsage(adjustQuantity: true);
    }

    /**
     * @param  array<int, array{question: string, answer: string, extra?: string|null}>  $flashcards
     */
    protected function persistAiFlashcards(array $flashcards): int
    {
        $disciplineId = $this->discipline->getKey();
        $count = 0;

        DB::transaction(function () use ($flashcards, $disciplineId, &$count): void {
            foreach ($flashcards as $card) {
                $question = $this->sanitizeText($card['question'] ?? '');
                $answer = $this->sanitizeText($card['answer'] ?? '');
                $extra = isset($card['extra']) ? $this->sanitizeText($card['extra']) : null;

                if ($question === '' || $answer === '') {
                    continue;
                }

                $finalAnswer = $this->formatAnswerWithExtra($answer, $extra);

                $note = Note::create([
                    'title' => Str::of($question)->limit(255, '')->value(),
                    'content' => $finalAnswer,
                    'is_flashcard' => true,
                    'flashcard_question' => Str::of($question)->limit(255, '')->value(),
                    'flashcard_answer' => $finalAnswer,
                    'discipline_id' => $disciplineId,
                ]);

                Log::create([
                    'action' => 'note.created',
                    'context' => [
                        'note_id' => $note->id,
                        'discipline_id' => $note->discipline_id,
                        'is_flashcard' => true,
                        'source' => 'ai_flashcards',
                    ],
                ]);

                $count++;
            }
        });

        return $count;
    }

    protected function sanitizeText(?string $value): string
    {
        $plain = trim(strip_tags((string) $value));

        return preg_replace('/\\s+/u', ' ', $plain) ?? '';
    }

    protected function formatAnswerWithExtra(string $answer, ?string $extra): string
    {
        if (blank($extra)) {
            return $answer;
        }

        return "{$answer}\n\n" . __('ai_flashcards.extra_note', ['extra' => $extra]);
    }

    protected function aiUsageService(): AiFlashcardsUsageService
    {
        return app(AiFlashcardsUsageService::class);
    }

    protected function generatorService(): GenerateFlashcardsService
    {
        return app(GenerateFlashcardsService::class);
    }

    protected function aiGeneratorRules(): array
    {
        return [
            'aiTopic' => ['required', 'string', 'min:5', 'max:255'],
            'aiDescription' => ['nullable', 'string', 'min:10', 'max:1200'],
            'aiQuantity' => [
                'required',
                'integer',
                Rule::in($this->aiQuantityOptions),
            ],
        ];
    }

    protected function aiGeneratorAttributes(): array
    {
        return [];
    }

    /**
     * @return array<int, int>
     */
    protected function resolveAiQuantityOptions(): array
    {
        $options = config('ai.flashcards.allowed_quantities', [5, 10, 15, 20]);

        $normalized = collect($options)
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $normalized ?: [5, 10, 15, 20];
    }

    protected function resetAiMessages(): void
    {
        $this->aiStatusMessage = null;
        $this->aiErrorMessage = null;
        $this->resetErrorBag();
    }
}
