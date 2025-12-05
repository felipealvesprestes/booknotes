<?php

use App\Exceptions\AiFlashcardsLimitException;
use App\Models\AiFlashcardsUsage;
use App\Models\User;
use App\Services\Ai\AiFlashcardsUsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    config(['app.timezone' => 'UTC']);
    Carbon::setTestNow(Carbon::parse('2024-03-10 15:30:00', 'UTC'));
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('returns existing usage and calculates remaining allowance for the day', function (): void {
    $user = User::factory()->create();

    $service = new AiFlashcardsUsageService(dailyLimit: 20);
    createUsageRecord($user, 12);

    $usage = $service->getUsageForToday($user);

    expect($usage->generated_count)->toBe(12)
        ->and($service->remainingForToday($user))->toBe(8);
});

it('provides an unsaved usage model when the user has no activity today', function (): void {
    $user = User::factory()->create();

    $service = new AiFlashcardsUsageService(dailyLimit: 15);
    $usage = $service->getUsageForToday($user);

    expect($usage->exists)->toBeFalse()
        ->and($usage->generated_count)->toBe(0)
        ->and($usage->getAttribute($usage->getUserForeignKey()))->toBe($user->id)
        ->and($service->remainingForToday($user))->toBe(15);
});

it('validates requested flashcard quantities against the remaining limit', function (): void {
    $user = User::factory()->create();
    $service = new AiFlashcardsUsageService(dailyLimit: 5);
    createUsageRecord($user, 3);

    $service->ensureWithinLimit($user, 2);

    expect(fn () => $service->ensureWithinLimit($user, 0))
        ->toThrow(\InvalidArgumentException::class);

    try {
        $service->ensureWithinLimit($user, 3);
    } catch (AiFlashcardsLimitException $exception) {
        expect($exception->dailyLimit)->toBe(5)
            ->and($exception->remaining())->toBe(2);

        return;
    }

    test()->fail('AiFlashcardsLimitException was not thrown when exceeding the limit.');
});

it('rejects increments smaller than one', function (): void {
    $user = User::factory()->create();
    $service = new AiFlashcardsUsageService();

    expect(fn () => $service->increment($user, 0))->toThrow(\InvalidArgumentException::class);
});

it('creates a usage record when incrementing for the first time', function (): void {
    $user = User::factory()->create();
    $service = new AiFlashcardsUsageService();

    $usage = $service->increment($user, 4);

    expect($usage->exists)->toBeTrue()
        ->and($usage->generated_count)->toBe(4)
        ->and($usage->date->toDateString())->toBe(Carbon::now()->toDateString());

    expect(AiFlashcardsUsage::count())->toBe(1);
});

it('increments existing usage rows atomically', function (): void {
    $user = User::factory()->create();
    $service = new AiFlashcardsUsageService();

    $existing = createUsageRecord($user, 2);
    $updated = $service->increment($user, 5);

    expect($updated->is($existing->fresh()))->toBeTrue()
        ->and($updated->generated_count)->toBe(7);
});

function createUsageRecord(User $user, int $generatedCount): AiFlashcardsUsage
{
    $now = Carbon::now();
    \DB::table('ai_flashcards_usages')->insert([
        'user_id' => $user->id,
        'date' => $now->toDateString(),
        'generated_count' => $generatedCount,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return AiFlashcardsUsage::query()
        ->where('user_id', $user->id)
        ->where('date', $now->toDateString())
        ->firstOrFail();
}
