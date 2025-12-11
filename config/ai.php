<?php

return [
    'flashcards' => [
        'daily_limit' => (int) env('AI_FLASHCARDS_DAILY_LIMIT', 50),
        'allowed_quantities' => [10, 15, 20],
        'pdf' => [
            'max_pages' => (int) env('AI_FLASHCARDS_PDF_MAX_PAGES', 30),
            'max_characters' => (int) env('AI_FLASHCARDS_PDF_MAX_CHARACTERS', 8000),
            'max_upload_kb' => (int) env('AI_FLASHCARDS_PDF_MAX_KB', 20480),
            'quantities' => [20, 30, 50],
        ],
    ],
];
