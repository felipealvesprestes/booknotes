<?php

use App\Exceptions\AiFlashcardGenerationException;
use App\Models\Discipline;
use App\Models\User;
use App\Services\Ai\GenerateFlashcardsService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    Http::preventStrayRequests();

    config([
        'services.openai.api_key' => 'test-key',
        'services.openai.base_url' => 'https://example.test',
        'services.openai.flashcards_model' => 'gpt-test',
        'services.openai.flashcards_max_output_tokens' => 600,
        'services.openai.timeout' => 10,
    ]);
});

it('fails when the OpenAI API key is missing', function (): void {
    Http::fake();
    config(['services.openai.api_key' => '']);

    $service = new GenerateFlashcardsService();

    expect(fn () => $service->generate(makeUser(), makeDiscipline(), 'Limits', null, 3))
        ->toThrow(AiFlashcardGenerationException::class, __('ai_flashcards.errors.missing_api'));
});

it('wraps transport failures into generation exceptions', function (): void {
    Http::fake(['*' => Http::response([], 503)]);

    $service = new GenerateFlashcardsService();

    expect(fn () => $service->generate(makeUser(), makeDiscipline(), 'Optics', null, 2))
        ->toThrow(AiFlashcardGenerationException::class, __('ai_flashcards.errors.unreachable'));
});

it('validates that the AI returned textual content', function (): void {
    Http::fake(['*' => Http::response([
        'choices' => [
            ['message' => ['content' => null]],
        ],
    ], 200)]);

    $service = new GenerateFlashcardsService();

    expect(fn () => $service->generate(makeUser(), makeDiscipline(), 'Trigonometry', null, 2))
        ->toThrow(AiFlashcardGenerationException::class, __('ai_flashcards.errors.empty_response'));
});

it('fails when the AI returns invalid JSON payloads', function (): void {
    Http::fake(['*' => Http::response([
        'choices' => [
            ['message' => ['content' => '{invalid-json']],
        ],
    ], 200)]);

    $service = new GenerateFlashcardsService();

    expect(fn () => $service->generate(makeUser(), makeDiscipline(), 'Geography', null, 2))
        ->toThrow(AiFlashcardGenerationException::class, __('ai_flashcards.errors.invalid_response'));
});

it('fails when no flashcards are produced', function (): void {
    Http::fake(['*' => Http::response([
        'choices' => [
            ['message' => ['content' => json_encode(['flashcards' => []])]],
        ],
    ], 200)]);

    $service = new GenerateFlashcardsService();

    expect(fn () => $service->generate(makeUser(), makeDiscipline(), 'Vectors', null, 2))
        ->toThrow(AiFlashcardGenerationException::class, __('ai_flashcards.errors.no_flashcards'));
});

it('returns normalized flashcards limited to the requested quantity', function (): void {
    $payload = [
        'flashcards' => [
            ['question' => '  What is ATP? ', 'answer' => ' Energy molecule ', 'extra' => '  Fuel '],
            ['question' => '', 'answer' => 'Should be ignored'],
            ['question' => 'Explain mitosis', 'answer' => ' cell division ', 'extra' => ''],
            ['question' => 'Spare card', 'answer' => 'Extra info'],
        ],
    ];

    Http::fake(['*' => Http::response([
        'choices' => [
            ['message' => ['content' => json_encode($payload)]],
        ],
    ], 200)]);

    $service = new GenerateFlashcardsService();

    $discipline = makeDiscipline('Biology');
    $user = makeUser(locale: 'pt_BR');

    $result = $service->generate($user, $discipline, 'Cellular energy', 'Focus on real-life usage', 2);

    expect($result)->toBe([
        ['question' => 'What is ATP?', 'answer' => 'Energy molecule', 'extra' => 'Fuel'],
        ['question' => 'Explain mitosis', 'answer' => 'cell division', 'extra' => null],
    ]);

    Http::assertSent(function (Request $request) use ($discipline) {
        $data = $request->data();

        return $request->url() === 'https://example.test/chat/completions'
            && $data['model'] === 'gpt-test'
            && $data['max_tokens'] === 600
            && $data['messages'][1]['content'] !== null
            && str_contains($data['messages'][1]['content'], $discipline->title)
            && str_contains($data['messages'][1]['content'], 'Cellular energy')
            && $data['response_format']['json_schema']['name'] === 'flashcards';
    });
});

function makeUser(string $locale = 'en'): User
{
    return User::factory()->make([
        'name' => 'Student Example',
        'locale' => $locale,
    ]);
}

function makeDiscipline(string $title = 'Physics'): Discipline
{
    return new Discipline(['title' => $title]);
}
