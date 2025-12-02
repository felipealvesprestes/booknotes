<?php

namespace App\Services\Ai;

use App\Exceptions\AiFlashcardsLimitException;
use App\Models\AiFlashcardsUsage;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AiFlashcardsUsageService
{
    public function __construct(
        protected ?int $dailyLimit = null,
    ) {
        $this->dailyLimit = $dailyLimit ?? (int) config('ai.flashcards.daily_limit', 50);
    }

    public function getDailyLimit(): int
    {
        return $this->dailyLimit;
    }

    public function getUsageForToday(User $user): AiFlashcardsUsage
    {
        $date = $this->currentDate();

        $usage = AiFlashcardsUsage::query()
            ->ownedBy($user)
            ->where('date', $date->toDateString())
            ->first();

        if ($usage) {
            return $usage;
        }

        $model = new AiFlashcardsUsage([
            'date' => $date->toDateString(),
            'generated_count' => 0,
        ]);

        $model->setAttribute($model->getUserForeignKey(), $user->getKey());

        return $model;
    }

    public function remainingForToday(User $user): int
    {
        $usage = $this->getUsageForToday($user);

        return max(0, $this->dailyLimit - $usage->generated_count);
    }

    public function ensureWithinLimit(User $user, int $requested): void
    {
        if ($requested <= 0) {
            throw new InvalidArgumentException('Requested flashcard amount must be at least 1.');
        }

        $usage = $this->getUsageForToday($user);

        if ($usage->generated_count + $requested > $this->dailyLimit) {
            throw new AiFlashcardsLimitException($this->dailyLimit, $usage->generated_count);
        }
    }

    public function increment(User $user, int $amount): AiFlashcardsUsage
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Flashcard increment must be greater than zero.');
        }

        $date = $this->currentDate();

        return DB::transaction(function () use ($user, $amount, $date) {
            $usage = AiFlashcardsUsage::query()
                ->ownedBy($user)
                ->where('date', $date->toDateString())
                ->lockForUpdate()
                ->first();

            if (! $usage) {
                $usage = new AiFlashcardsUsage([
                    'date' => $date->toDateString(),
                    'generated_count' => 0,
                ]);

                $usage->setAttribute($usage->getUserForeignKey(), $user->getKey());
            }

            $usage->generated_count += $amount;
            $usage->save();

            return $usage->refresh();
        });
    }

    protected function currentDate(): Carbon
    {
        return now()->setTimezone(config('app.timezone', 'UTC'))->startOfDay();
    }
}
