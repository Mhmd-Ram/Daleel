<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * View the current user's profile details.
     */
    public function show(): View
    {
        $user = auth()->user()->loadCount('registrations');

        return view('profile.show', ['user' => $user]);
    }

    /**
     * Show the form to edit the current user's profile (optional feature).
     */
    public function edit(): View
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    /**
     * Update the current user's profile details.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('profile.show')->with('success', 'Profile updated.');
    }
}
