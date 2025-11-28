<?php

use App\Http\Middleware\EnsureSubscribed;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

uses(TestCase::class);

test('middleware aborts with 402 when request expects json', function (): void {
    $request = Request::create('/protected', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $request->setUserResolver(fn () => new class {
        public function hasActiveSubscriptionOrTrial(): bool
        {
            return false;
        }
    });

    $middleware = new EnsureSubscribed();

    $next = fn () => new Response('ok');

    $this->expectExceptionMessage(__('A assinatura é necessária para continuar.'));

    $middleware->handle($request, $next);
});

test('middleware redirects to billing page for html requests', function (): void {
    Route::get('/billing', fn () => 'billing')->name('settings.billing');

    $session = app('session')->driver('array');
    $session->start();

    $request = Request::create('/protected', 'GET');
    $request->setLaravelSession($session);
    $request->setUserResolver(fn () => new class {
        public function hasActiveSubscriptionOrTrial(): bool
        {
            return false;
        }
    });

    $middleware = new EnsureSubscribed();

    $response = $middleware->handle($request, fn () => new Response('ok'));

    expect($response->getTargetUrl())->toBe(route('settings.billing'))
        ->and($session->get('subscription_required'))->toBe(__('Sua assinatura precisa ser ativada para continuar usando o Booknotes.'));
});
