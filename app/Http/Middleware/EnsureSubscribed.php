<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscribed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasActiveSubscriptionOrTrial()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(Response::HTTP_PAYMENT_REQUIRED, __('A assinatura é necessária para continuar.'));
        }

        return redirect()
            ->route('settings.billing')
            ->with('subscription_required', __('Sua assinatura precisa ser ativada para continuar usando o Booknotes.'));
    }
}
