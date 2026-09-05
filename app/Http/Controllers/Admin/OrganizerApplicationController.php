<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizerApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrganizerApplicationController extends Controller
{
    /**
     * The review queue: everything still pending, plus recent decisions.
     */
    public function index(): View
    {
        return view('admin.organizer-applications.index', [
            'pending' => OrganizerApplication::with('user')->pending()->oldest()
                ->paginate(20, ['*'], 'pending_page'),
            // Named page parameters: two paginators share this page, and the
            // default `page` would move both at once. `limit(20)` used to hide
            // the 21st decision outright.
            'reviewed' => OrganizerApplication::with(['user', 'reviewer'])
                ->whereNotNull('reviewed_at')
                ->latest('reviewed_at')
                ->paginate(20, ['*'], 'reviewed_page'),
        ]);
    }

    /**
     * Approve an application and promote the applicant to organizer.
     */
    public function approve(OrganizerApplication $application): RedirectResponse
    {
        $this->abortUnlessPending($application);

        $application->approve(Auth::guard('admin')->user());

        return back()->with('success', __('app.flash.now_an_organizer', ['name' => $application->user->name]));
    }

    /**
     * Reject an application. The user may apply again.
     */
    public function reject(OrganizerApplication $application): RedirectResponse
    {
        $this->abortUnlessPending($application);

        $application->reject(Auth::guard('admin')->user());

        return back()->with('success', __('app.flash.application_rejected'));
    }

    /**
     * Guard against a second decision on an already-reviewed application,
     * which two admins working the queue at once could otherwise trigger.
     */
    private function abortUnlessPending(OrganizerApplication $application): void
    {
        abort_unless($application->isPending(), 409, 'This application has already been reviewed.');
    }
}
