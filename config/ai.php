<?php

return [
    'flashcards' => [
        'daily_limit' => (int) env('AI_FLASHCARDS_DAILY_LIMIT', 50),
        'allowed_quantities' => [10, 15, 20],
    ],
];
