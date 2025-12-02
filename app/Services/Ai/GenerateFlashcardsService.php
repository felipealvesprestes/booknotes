<?php

namespace App\Services\Ai;

use App\Exceptions\AiFlashcardGenerationException;
use App\Models\Discipline;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use JsonException;

class GenerateFlashcardsService
{
    protected string $apiKey;

    protected string $baseUrl;

    protected string $model;

    protected int $maxOutputTokens;

    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = (string) config('services.openai.api_key', '');
        $this->baseUrl = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');
        $this->model = (string) config('services.openai.flashcards_model', 'gpt-4o-mini');
        $this->maxOutputTokens = (int) config('services.openai.flashcards_max_output_tokens', 1200);
        $this->timeout = (int) config('services.openai.timeout', 30);
    }

    /**
     * @return array<int, array{question: string, answer: string, extra?: string|null}>
     */
    public function generate(
        User $user,
        Discipline $discipline,
        string $topic,
        ?string $description,
        int $quantity,
    ): array {
        if ($this->apiKey === '') {
            throw new AiFlashcardGenerationException(__('ai_flashcards.errors.missing_api'));
        }

        $payload = [
            'model' => $this->model,
            'temperature' => 0.4,
            'max_tokens' => $this->maxOutputTokens,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt($user->locale ?? app()->getLocale()),
                ],
                [
                    'role' => 'user',
                    'content' => $this->userPrompt($discipline, $topic, $description, $quantity, $user->locale),
                ],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'flashcards',
                    'schema' => [
                        'type' => 'array',
                        'minItems' => 1,
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'question' => ['type' => 'string'],
                                'answer' => ['type' => 'string'],
                                'extra' => ['type' => 'string'],
                            ],
                            'required' => ['question', 'answer'],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withToken($this->apiKey)
                ->baseUrl($this->baseUrl)
                ->acceptJson()
                ->timeout($this->timeout)
                ->post('/chat/completions', $payload)
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw new AiFlashcardGenerationException(
                __('ai_flashcards.errors.unreachable'),
                $exception->getCode(),
                $exception,
            );
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content)) {
            throw new AiFlashcardGenerationException(__('ai_flashcards.errors.empty_response'));
        }

        try {
        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new AiFlashcardGenerationException(
                __('ai_flashcards.errors.invalid_response'),
                $exception->getCode(),
                $exception,
            );
        }

        if (! is_array($decoded) || empty($decoded)) {
            throw new AiFlashcardGenerationException(__('ai_flashcards.errors.no_flashcards'));
        }

        return collect($decoded)
            ->filter(fn ($card) => is_array($card))
            ->map(function (array $card) {
                $question = trim((string) ($card['question'] ?? ''));
                $answer = trim((string) ($card['answer'] ?? ''));
                $extra = isset($card['extra']) ? trim((string) $card['extra']) : null;

                return [
                    'question' => $question,
                    'answer' => $answer,
                    'extra' => $extra !== '' ? $extra : null,
                ];
            })
            ->filter(fn (array $card) => $card['question'] !== '' && $card['answer'] !== '')
            ->take($quantity)
            ->values()
            ->all();
    }

    protected function systemPrompt(?string $locale): string
    {
        $language = $this->humanReadableLanguage($locale);

        return <<<PROMPT
You are an expert study coach that produces concise, high quality flashcards in {$language}. Stick strictly to the topic, avoid hallucinations, and return only valid JSON.
- Flashcards must be factual, aligned with the specified discipline context, and appropriate for students.
- Each question must capture one concept, and answers should stay under 80 words.
- Include an optional "extra" string only if a short mnemonic, context, or caution truly helps memorization.
- DO NOT output any text outside the JSON payload.
PROMPT;
    }

    protected function userPrompt(
        Discipline $discipline,
        string $topic,
        ?string $description,
        int $quantity,
        ?string $locale,
    ): string {
        $language = $this->humanReadableLanguage($locale);
        $trimmedDescription = trim((string) $description);
        $descriptionLine = $trimmedDescription !== ''
            ? "Study focus: {$trimmedDescription}"
            : 'Study focus: keep content general for the topic.';

        return <<<PROMPT
Discipline: {$discipline->title}
Topic: {$topic}
{$descriptionLine}
Quantity: {$quantity}
Language: Write both questions and answers in {$language}.
Return a JSON array where every item has "question", "answer", and optional "extra".
PROMPT;
    }

    protected function humanReadableLanguage(?string $locale): string
    {
        $normalized = strtolower((string) $locale);

        return match ($normalized) {
            'pt', 'pt_br', 'pt-br', 'pt-br@utf-8' => 'português brasileiro',
            default => 'English',
        };
    }
}
