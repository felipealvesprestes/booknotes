<?php

namespace App\Exceptions;

use RuntimeException;

class AiFlashcardsLimitException extends RuntimeException
{
    public function __construct(
        public readonly int $dailyLimit,
        public readonly int $usedToday,
        ?string $message = null,
    ) {
        parent::__construct($message ?? 'AI flashcard daily limit exceeded.');
    }

    public function remaining(): int
    {
        return max(0, $this->dailyLimit - $this->usedToday);
    }
}
