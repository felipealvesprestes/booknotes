<?php

return [
    'supported' => [
        'en' => [
            'label' => 'English',
            'native_label' => 'English',
        ],
        'pt_BR' => [
            'label' => 'Portuguese',
            'native_label' => 'Portugues',
        ],
    ],

    'default' => env('LOCALIZATION_DEFAULT', 'pt_BR'),
];
