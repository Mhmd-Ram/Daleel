<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrganizerApplicationRequest;
use Illuminate\Http\RedirectResponse;

class OrganizerApplicationController extends Controller
{
    /**
     * Apply to become an event organizer. The form lives on the profile page.
     */
    public function store(StoreOrganizerApplicationRequest $request): RedirectResponse
    {
        $request->user()->organizerApplications()->create($request->validated());

        return redirect()->route('profile.show')
            ->with('success', __('app.flash.application_sent'));
    }
}
