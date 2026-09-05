<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Lets an admin browse the public site as a real user, and stop again.
 *
 * The two guards share one session, so an admin can hold both at once: the
 * `admin` session keeps the admin area open while the `web` session gives them
 * the same standing as any visitor. That is what makes registering for an
 * event, applying as an organizer, or filing a report actually work - each of
 * those writes a foreign key that has to point at a real `users` row.
 *
 * A route of its own rather than logging both guards in at sign-in: an admin
 * who never looks at the public site never has a user row created for them, and
 * the two sessions can be ended independently.
 */
class ViewSiteController extends Controller
{
    /**
     * Sign the current admin into the web guard and send them to the site.
     */
    public function store(): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        Auth::guard('web')->login($admin->ensureSiteUser());

        return redirect()->route('home');
    }

    /**
     * Leave site mode, keeping the admin session.
     */
    public function destroy(): RedirectResponse
    {
        // The web guard only. `session()->invalidate()` here would take the
        // admin session with it and bounce them to the login screen - the two
        // guards share one session.
        Auth::guard('web')->logout();

        return redirect()->route('admin.dashboard');
    }
}
