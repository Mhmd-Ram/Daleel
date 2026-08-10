<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * List all active events, optionally filtered by category.
     */
    public function index(Request $request): View
    {
        $events = Event::with('category')
            ->where('is_active', true)
            ->where('end_date_time', '>', now())
            ->when($request->integer('category'), fn ($query, $id) => $query->where('category_id', $id))
            ->orderBy('start_date_time')
            ->paginate(12)
            ->withQueryString();

        return view('events.index', [
            'events' => $events,
            'categories' => Category::orderBy('name')->get(),
            'selectedCategory' => $request->integer('category'),
        ]);
    }

    /**
     * Show a single event's full details.
     */
    public function show(Event $event): View
    {
        abort_unless($event->is_active, 404);

        $event->loadCount('registeredUsers');

        $isRegistered = auth()->check()
            && $event->registeredUsers()->where('user_id', auth()->id())->exists();

        return view('events.show', [
            'event' => $event,
            'isRegistered' => $isRegistered,
        ]);
    }
}
