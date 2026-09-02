<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * View the current user's profile details.
     */
    public function show(): View
    {
        $user = auth()->user()->loadCount('registrations');

        return view('profile.show', [
            'user' => $user,
            // The relation is ordered newest-first, so this is the latest decision
            // or the one still awaiting review.
            'organizerApplication' => $user->organizerApplications()->first(),
        ]);
    }

    /**
     * Show the form to edit the current user's profile (optional feature).
     */
    public function edit(): View
    {
        // Counted here rather than in the view: the danger zone names how many
        // events deletion would take with it, and a view should not run queries.
        return view('profile.edit', [
            'user' => auth()->user()->loadCount('organizedEvents'),
        ]);
    }

    /**
     * Update the current user's profile details.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('profile.show')->with('success', 'Profile updated.');
    }

    /**
     * Change the signed-in user's password (SRS FR-3.2, FR-3.3).
     *
     * The current password is re-checked in UpdatePasswordRequest, so an unlocked
     * browser is not enough to take an account over.
     *
     * This deliberately does not call Auth::logoutOtherDevices(). That needs the
     * AuthenticateSession middleware, which keys its session entry on the default
     * guard; this app switches the default between `web` and `admin`, so enabling
     * it signs people out whenever they cross between the two areas. Other
     * sessions therefore survive a password change here.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->forceFill([
            'password' => $request->validated('password'),
        ])->save();

        return redirect()->route('profile.show')->with('success', 'Password updated.');
    }

    /**
     * Permanently delete the signed-in user's account (SRS UC4, FR-3.4 - FR-3.6).
     *
     * The password prompt is the confirmation step the use case asks for. The
     * cascades do the rest: registrations, organizer applications and - for an
     * organizer - the events they own all go with the row. Deleting those events
     * also deletes everyone else's saved entries on them, so the form warns about
     * it by name before the user gets here.
     */
    public function destroy(DeleteAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Log out before deleting, or the session guard tries to re-resolve a row
        // that no longer exists.
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()->route('home')->with('success', 'Your account has been deleted.');
    }
}
