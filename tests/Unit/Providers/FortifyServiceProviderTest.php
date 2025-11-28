<?php

use App\Http\Responses\VerifyEmailResponse;
use App\Providers\FortifyServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Tests\TestCase;

uses(TestCase::class);

it('binds the custom verify email response contract', function (): void {
    $provider = new FortifyServiceProvider($this->app);
    $provider->register();

    $resolved = $this->app->make(VerifyEmailResponseContract::class);

    expect($resolved)->toBeInstanceOf(VerifyEmailResponse::class);
});

it('limits two factor challenges per login session', function (): void {
    $provider = new FortifyServiceProvider($this->app);
    $provider->register();
    $provider->boot();

    $session = app('session.store');
    $session->start();

    $request = Request::create('/two-factor-challenge', 'POST');
    $request->setLaravelSession($session);
    $request->session()->put('login.id', 'session-123');

    $limiter = RateLimiter::limiter('two-factor');
    $limit = $limiter($request);

    expect($limit)->toBeInstanceOf(Limit::class)
        ->and($limit->key)->toBe('session-123')
        ->and($limit->maxAttempts)->toBe(5)
        ->and($limit->decaySeconds)->toBe(60);
});

it('limits login attempts using normalized username and ip address', function (): void {
    $provider = new FortifyServiceProvider($this->app);
    $provider->register();
    $provider->boot();

    $request = Request::create(
        '/login',
        'POST',
        ['email' => 'ExampleUser@Email.COM'],
        server: ['REMOTE_ADDR' => '203.0.113.50']
    );

    $limiter = RateLimiter::limiter('login');
    $limit = $limiter($request);

    expect($limit)->toBeInstanceOf(Limit::class)
        ->and($limit->key)->toBe('exampleuser@email.com|203.0.113.50')
        ->and($limit->maxAttempts)->toBe(5)
        ->and($limit->decaySeconds)->toBe(60);
});
