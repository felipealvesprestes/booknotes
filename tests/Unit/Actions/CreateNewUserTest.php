<?php

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('grants lifetime access when email is configured', function (): void {
    config([
        'services.stripe.lifetime_emails' => ['vip@example.com'],
        'services.stripe.trial_days' => 14,
        'localization.default' => 'pt_BR',
    ]);

    Carbon::setTestNow('2024-01-01 10:00:00');

    $action = new CreateNewUser();

    $user = $action->create([
        'name' => 'VIP User',
        'email' => 'vip@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->is_lifetime)->toBeTrue()
        ->and($user->trial_starts_at)->toEqual(Carbon::now())
        ->and($user->trial_ends_at)->toBeNull();

    Carbon::setTestNow();
});

it('sets trial dates when user is not in lifetime list', function (): void {
    config([
        'services.stripe.lifetime_emails' => [],
        'services.stripe.trial_days' => 10,
        'localization.default' => 'pt_BR',
    ]);

    Carbon::setTestNow('2024-02-01 08:00:00');

    $action = new CreateNewUser();

    $user = $action->create([
        'name' => 'Regular User',
        'email' => 'regular@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    expect((bool) $user->is_lifetime)->toBeFalse()
        ->and($user->trial_starts_at)->toEqual(Carbon::now())
        ->and($user->trial_ends_at)->toEqual(Carbon::now()->addDays(10));

    Carbon::setTestNow();
});
