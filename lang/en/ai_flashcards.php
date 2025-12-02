<?php

return [
    'generated' => '{1} :count flashcard was generated with AI and added to :discipline.|[2,*] :count flashcards were generated with AI and added to :discipline.',
    'limit_remaining' => '{1} You can only generate :count more flashcard today.|[2,*] You can only generate :count more flashcards today.',
    'limit_reached' => 'You reached the daily AI flashcard limit. Try again tomorrow.',
    'used_today' => '{1} You have generated :count flashcard with AI today.|[2,*] You have generated :count flashcards with AI today.',
    'remaining_today' => '{1} You can still generate :count flashcard today.|[2,*] You can still generate :count flashcards today.',
    'remaining_after' => '{1} :count flashcard left today.|[2,*] :count flashcards left today.',
    'no_response' => 'AI did not return any flashcards. Adjust the topic and try again.',
    'save_error' => 'Could not save the generated flashcards. Please try again.',
    'extra_note' => 'Extra note: :extra',
    'errors' => [
        'missing_api' => 'OpenAI API key is missing. Ask support to configure it before generating flashcards.',
        'unreachable' => 'Could not reach the AI service. Please try again in a moment.',
        'empty_response' => 'AI response was empty. Try another request.',
        'invalid_response' => 'Could not understand the AI response. Please try again.',
        'no_flashcards' => 'AI response did not contain valid flashcards.',
    ],
];
