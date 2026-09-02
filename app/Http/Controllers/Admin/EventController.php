<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Event management for administrators.
 *
 * Unlike the organizer area, this is deliberately not ownership-scoped: the SRS
 * makes the administrator the moderator of all content (FR-12.2, FR-12.3), so
 * admins can review, unpublish and remove any event, including ones organizers
 * created. `store()` still stamps the creating admin, so ownership is never lost.
 */
class EventController extends Controller
{
    /**
     * Every event on the platform, whoever created it (SRS FR-12.2).
     *
     * Deliberately not scoped to the signed-in admin's own events. The owner
     * column tells them apart.
     */
    public function index(Request $request): View
    {
        $owner = $this->owner($request);
        $status = $this->status($request);

        $events = Event::with(['category', 'admin', 'organizer'])
            ->withCount(['registeredUsers', 'reports'])
            ->when($owner === 'admin', fn ($query) => $query->whereNotNull('admin_id'))
            ->when($owner === 'organizer', fn ($query) => $query->whereNotNull('organizer_id'))
            ->when($status, fn ($query, $value) => $query->where('is_active', $value === 'published'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.events.index', [
            'events' => $events,
            'selectedOwner' => $owner,
            'selectedStatus' => $status,
        ]);
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
            ->with('success', __('app.flash.event_created'));
    }

    /**
     * Show the form to edit an event.
     */
    public function edit(Event $event): View
    {
        return view('admin.events.edit', [
            'event' => $event,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Update an event.
     *
     * Neither owner column is in StoreEventRequest::rules(), so a moderating
     * admin editing an organizer's event cannot take ownership of it and the
     * Event::saving invariant stays satisfied.
     */
    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $event->update($request->validated());

        return redirect()->route('admin.events.index')
            ->with('success', __('app.flash.event_updated'));
    }

    /**
     * Delete an event.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', __('app.flash.event_deleted'));
    }

    /**
     * Publish / unpublish an event via the is_active flag.
     */
    public function togglePublish(Event $event): RedirectResponse
    {
        $event->update(['is_active' => ! $event->is_active]);

        return back()->with('success', $event->is_active ? __('app.flash.event_published') : __('app.flash.event_unpublished'));
    }

    /**
     * Show the users registered for an event.
     */
    public function registrations(Event $event): View
    {
        $event->load('registeredUsers');

        return view('admin.events.registrations', ['event' => $event]);
    }

    /**
     * The chosen owner-type filter, or null when it is absent or not one of ours.
     */
    private function owner(Request $request): ?string
    {
        $owner = (string) $request->query('owner', '');

        return in_array($owner, ['admin', 'organizer'], true) ? $owner : null;
    }

    /**
     * The chosen publish-status filter, or null when it is not one of ours.
     */
    private function status(Request $request): ?string
    {
        $status = (string) $request->query('status', '');

        return in_array($status, ['published', 'draft'], true) ? $status : null;
    }
}
