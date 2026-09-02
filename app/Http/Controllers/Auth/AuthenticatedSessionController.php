<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the user login form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Authenticate a user.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        // A ban is checked after the credentials, not before: answering "banned"
        // to a wrong password would confirm to an attacker that the account
        // exists (SRS FR-1.5, UC2 exception E2).
        if (Auth::guard('web')->user()->isBanned()) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => __('This account has been banned. Contact the site administrators if you believe this is a mistake.'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    /**
     * Log the user out.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
