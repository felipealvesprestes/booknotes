<?php

$stripeLifetimeDefaults = [
    'felipealvesprestes@gmail.com',
    'gabrielakrauzerprestes@gmail.com',
];

$stripeLifetimeFromEnv = array_filter(array_map(
    static fn ($email) => trim($email),
    explode(',', (string) env('SUBSCRIPTION_LIFETIME_EMAILS', ''))
));

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'price_id' => env('STRIPE_PRICE_ID'),
        'trial_days' => (int) env('SUBSCRIPTION_TRIAL_DAYS', 14),
        'plan_name' => env('SUBSCRIPTION_PLAN_NAME', 'Acesso Plataforma Booknotes'),
        'monthly_amount' => (float) env('SUBSCRIPTION_MONTHLY_AMOUNT', 14.90),
        'lifetime_emails' => array_values(array_unique(array_merge(
            $stripeLifetimeDefaults,
            $stripeLifetimeFromEnv,
        ))),
    ],

];
