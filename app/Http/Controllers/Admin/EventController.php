<?php

namespace App\Http\Controllers\Admin;

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
     * List the current admin's events with category and active status.
     */
    public function index(): View
    {
        $events = Event::with('category')
            ->where('admin_id', Auth::guard('admin')->id())
            ->withCount('registeredUsers')
            ->latest()
            ->get();

        return view('admin.events.index', ['events' => $events]);
    }

    /**
     * Show the form to create an event.
     */
    public function create(): View
    {
        return view('admin.events.create', ['categories' => Category::orderBy('name')->get()]);
    }

    /**
     * Store a new event owned by the current admin.
     */
    public function store(StoreEventRequest $request): RedirectResponse
    {
        $event = new Event($request->validated());
        $event->admin_id = Auth::guard('admin')->id();
        $event->save();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created.');
    }

    /**
     * Show the form to edit an event.
     */
    public function edit(Event $event): View
    {
        $this->authorizeOwner($event);

        return view('admin.events.edit', [
            'event' => $event,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Update an event.
     */
    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $this->authorizeOwner($event);

        $event->update($request->validated());

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated.');
    }

    /**
     * Delete an event.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $this->authorizeOwner($event);

        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted.');
    }

    /**
     * Publish / unpublish an event via the is_active flag.
     */
    public function togglePublish(Event $event): RedirectResponse
    {
        $this->authorizeOwner($event);

        $event->update(['is_active' => ! $event->is_active]);

        return back()->with('success', $event->is_active ? 'Event published.' : 'Event unpublished.');
    }

    /**
     * Show the users registered for an event.
     */
    public function registrations(Event $event): View
    {
        $this->authorizeOwner($event);

        $event->load('registeredUsers');

        return view('admin.events.registrations', ['event' => $event]);
    }

    /**
     * Ensure the current admin owns the given event.
     */
    private function authorizeOwner(Event $event): void
    {
        abort_unless($event->admin_id === Auth::guard('admin')->id(), 403);
    }
}
