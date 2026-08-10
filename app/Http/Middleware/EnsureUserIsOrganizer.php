<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict the organizer area to users an admin has promoted.
 */
class EnsureUserIsOrganizer
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isOrganizer(), 403, 'You are not an event organizer.');

        return $next($request);
    }
}
