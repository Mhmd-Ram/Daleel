<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Apply the visitor's chosen language for the rest of the request.
 *
 * The choice lives in the session rather than the URL: the site has no
 * per-language routes, and a session keeps every existing link working.
 *
 * Dates and counts deliberately stay in Latin digits in both languages. That
 * is a readability choice for this project, matching how the owner writes
 * mixed Arabic and English text, so Carbon's locale is never switched and no
 * digit-localising helper is used.
 */
class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (in_array($locale, config('app.supported_locales'), true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
