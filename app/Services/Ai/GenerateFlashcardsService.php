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
                        '$schema' => 'https://json-schema.org/draft/2020-12/schema',
                        'type' => 'object',
                        'properties' => [
                            'flashcards' => [
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
                        'required' => ['flashcards'],
                        'additionalProperties' => false,
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
        } catch (ConnectionException | RequestException $exception) {
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

        $cards = $decoded['flashcards'] ?? (is_array($decoded) ? $decoded : null);

        if (! is_array($cards) || empty($cards)) {
            throw new AiFlashcardGenerationException(__('ai_flashcards.errors.no_flashcards'));
        }

        return collect($cards)
            ->filter(fn($card) => is_array($card))
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
            ->filter(fn(array $card) => $card['question'] !== '' && $card['answer'] !== '')
            ->take($quantity)
            ->values()
            ->all();
    }

    protected function systemPrompt(?string $locale): string
    {
        $language = $this->humanReadableLanguage($locale);

        return <<<PROMPT
You generate study flashcards in {$language}.

Rules:
- Use only factual content related to the given discipline and topic.
- Each flashcard covers ONE clear idea.
- "question" is short and direct.
- "answer" is correct and concise (max ~60–80 words).
- "extra" is optional, use only for a short tip, mnemonic or warning.
- Output ONLY a JSON array of objects with keys: "question", "answer", "extra".
- Do NOT write explanations, comments or text outside the JSON.
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
            : 'Study focus: keep the content general for this topic, without leaving it.';

        return <<<PROMPT
Language for questions and answers: {$language}
Discipline: {$discipline->title}
Topic: {$topic}
{$descriptionLine}
Number of flashcards to generate: {$quantity}

Generate EXACTLY {$quantity} flashcards about this topic, suitable for a student revising this discipline.

Return ONLY the JSON array, where each item has:
- "question": string
- "answer": string
- "extra": string (can be empty if not needed)
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
