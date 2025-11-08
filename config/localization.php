<?php

return [
    'supported' => [
        'en' => [
            'label' => 'English',
            'native_label' => 'English',
        ],
        'pt_BR' => [
            'label' => 'Portuguese (Brazil)',
            'native_label' => 'Portugues (Brasil)',
        ],
    ],

    'default' => env('APP_LOCALE', 'en'),
];
