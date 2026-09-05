<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function create(): View
    {
        return view('admin.auth.login');
    }

    /**
     * Authenticate an admin against the separate "admin" guard.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('app.auth.credentials_mismatch'),
            ]);
        }

        $request->session()->regenerate();

        // The dashboard, not the event list: it is the overview an admin wants
        // first. `intended()` still wins, so a deep link they were bounced from
        // survives the detour through the login screen.
        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Log the admin out.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        // Ending the admin session ends the site-mode session it created. Said
        // out loud rather than left to invalidate() as a side effect.
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
