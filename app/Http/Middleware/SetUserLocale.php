<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = array_keys(config('localization.supported', []));
        $defaultLocale = config('app.locale', 'en');

        $preferred = $request->user()?->locale ?? $request->session()->get('locale') ?? $defaultLocale;

        if (! in_array($preferred, $availableLocales, true)) {
            $preferred = $defaultLocale;
        }

        App::setLocale($preferred);

        return $next($request);
    }
}
