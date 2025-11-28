<?php

use App\Http\Middleware\SetUserLocale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('middleware falls back to default locale when preferred is unsupported', function (): void {
    config(['localization.supported' => ['en' => 'English']]);
    config(['app.locale' => 'en']);

    $user = User::factory()->create(['locale' => 'it']);

    $request = Request::create('/test');
    $request->setUserResolver(fn () => $user);

    $middleware = new SetUserLocale();

    $response = $middleware->handle($request, function () {
        return new Response('OK');
    });

    expect(App::getLocale())->toBe('en')
        ->and($response->getContent())->toBe('OK');
});
