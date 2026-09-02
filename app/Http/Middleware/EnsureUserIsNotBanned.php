<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Turn a ban into an immediate logout.
 *
 * The login screen already refuses banned accounts, but a ban applied while
 * someone is signed in would otherwise not take effect until their session
 * expired. This closes that window on the next request they make.
 */
class EnsureUserIsNotBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isBanned()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'This account has been banned.');
        }

        return $next($request);
    }
}
