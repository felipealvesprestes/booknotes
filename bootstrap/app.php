<?php

use App\Http\Middleware\EnsureSubscribed;
use App\Http\Middleware\SetUserLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'subscribed' => EnsureSubscribed::class,
        ]);

        $middleware->web(append: [
            SetUserLocale::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $expiredSessionResponse = function (Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Sua sessão expirou, por favor faça login novamente.'),
                ], 419);
            }

            return to_route('login')->with('toast', [
                'title' => __('Sessão expirada'),
                'message' => __('Sua sessão foi encerrada. Faça login novamente para continuar.'),
                'variant' => 'warning',
            ]);
        };

        $exceptions->render(function (TokenMismatchException $exception, Request $request) use ($expiredSessionResponse) {
            return $expiredSessionResponse($request);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) use ($expiredSessionResponse) {
            if ($exception->getStatusCode() === 419) {
                return $expiredSessionResponse($request);
            }
        });
    })->create();
