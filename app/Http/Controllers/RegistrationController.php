<?php

namespace App\Http\Controllers;

use App\Mail\EventRegistered;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    /**
     * "My Events" — the events the current user has registered for.
     */
    public function index(): View
    {
        $events = auth()->user()
            ->registrations()
            ->with('category')
            ->orderBy('start_date_time')
            ->get();

        return view('my-events', ['events' => $events]);
    }

    /**
     * Register the current user for an event.
     *
     * Enforces the brief's rules: only active, not-yet-finished events; one
     * registration per user per event; and (optionally) the capacity limit.
     */
    public function store(Event $event): RedirectResponse
    {
        if (! $event->is_active || $event->hasFinished()) {
            return back()->with('error', 'Registration for this event is closed.');
        }

        $alreadyRegistered = $event->registeredUsers()
            ->where('user_id', auth()->id())
            ->exists();

        if ($alreadyRegistered) {
            return back()->with('error', 'You are already registered for this event.');
        }

        if ($event->isFull()) {
            return back()->with('error', 'This event has reached its capacity.');
        }

        $user = auth()->user();

        $event->registeredUsers()->attach($user->id, ['created_at' => now()]);

        Mail::to($user)->queue(new EventRegistered($user, $event));

        return back()->with('success', 'You are registered for this event. A confirmation email is on its way.');
    }

    /**
     * Cancel the current user's registration for an event.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $event->registeredUsers()->detach(auth()->id());

        return back()->with('success', 'Your registration has been cancelled.');
    }
}
