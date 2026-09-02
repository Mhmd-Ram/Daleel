<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * List the events this organizer owns.
     */
    public function index(): View
    {
        $events = Auth::user()->organizedEvents()
            ->with('category')
            ->withCount('registeredUsers')
            ->latest()
            ->get();

        return view('organizer.events.index', ['events' => $events]);
    }

    /**
     * Show the form to create an event.
     */
    public function create(): View
    {
        return view('organizer.events.create', ['categories' => Category::orderBy('name')->get()]);
    }

    /**
     * Store a new event owned by the current organizer.
     */
    public function store(StoreEventRequest $request): RedirectResponse
    {
        $event = new Event($request->validated());
        $event->organizer_id = Auth::id();
        $event->save();

        return redirect()->route('organizer.events.index')
            ->with('success', __('app.flash.event_created'));
    }

    /**
     * Show the form to edit one of this organizer's events.
     */
    public function edit(Event $event): View
    {
        $this->authorizeOwner($event);

        return view('organizer.events.edit', [
            'event' => $event,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Update one of this organizer's events.
     */
    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $this->authorizeOwner($event);

        $event->update($request->validated());

        return redirect()->route('organizer.events.index')
            ->with('success', __('app.flash.event_updated'));
    }

    /**
     * Delete one of this organizer's events.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $this->authorizeOwner($event);

        $event->delete();

        return redirect()->route('organizer.events.index')
            ->with('success', __('app.flash.event_deleted'));
    }

    /**
     * Ensure the event belongs to the current organizer. Admin-owned events
     * are off limits here, as are other organizers' events.
     */
    private function authorizeOwner(Event $event): void
    {
        abort_unless($event->isOwnedBy(Auth::user()), 403);
    }
}
