<?php

namespace App\Http\Controllers;

use App\Mail\EventRegistered;
use App\Models\Event;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    /**
     * How many events a single calendar cell shows before collapsing the rest
     * into a "+n more" line.
     */
    private const EVENTS_PER_DAY_CELL = 3;

    /**
     * "My Events": the events the current user has registered for, as a list
     * and as a month calendar.
     */
    public function index(Request $request): View
    {
        $events = auth()->user()
            ->registrations()
            ->with('category')
            ->orderBy('start_date_time')
            ->get();

        $month = $this->resolveMonth($request->query('month'));

        return view('my-events', [
            'events' => $events,
            'month' => $month,
            'weeks' => $this->calendarWeeks($month, $events),
            'previousMonth' => $month->subMonth()->format('Y-m'),
            'nextMonth' => $month->addMonth()->format('Y-m'),
            'eventsPerDayCell' => self::EVENTS_PER_DAY_CELL,
        ]);
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

    /**
     * Turn a `?month=YYYY-MM` query value into the first day of that month.
     *
     * The query string is user-editable, so anything malformed falls back to the
     * current month instead of erroring. The regex, not a try/catch, is what
     * rejects bad input: it pins the month to 01-12 so `2026-13` cannot roll over
     * into the next year. The parameter is `mixed` because Laravel hands back an
     * array for `?month[]=x`.
     */
    private function resolveMonth(mixed $requested): CarbonImmutable
    {
        if (! is_string($requested) || preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $requested, $matches) !== 1) {
            return CarbonImmutable::now()->startOfMonth();
        }

        return CarbonImmutable::create((int) $matches[1], (int) $matches[2], 1)->startOfDay();
    }

    /**
     * Build the month grid as whole Sunday-to-Saturday weeks.
     *
     * The grid always starts on a Sunday and ends on a Saturday, so it stays
     * rectangular; days from the neighbouring months fill the corners and are
     * flagged with `inMonth = false` so the view can mute them.
     *
     * @param  Collection<int, Event>  $events
     * @return Collection<int, Collection<int, array{date: CarbonImmutable, inMonth: bool, isToday: bool, events: Collection<int, Event>}>>
     */
    private function calendarWeeks(CarbonImmutable $month, Collection $events): Collection
    {
        $gridStart = $month->startOfWeek(CarbonInterface::SUNDAY);
        $gridEnd = $month->endOfMonth()->endOfWeek(CarbonInterface::SATURDAY);

        $eventsByDate = $this->eventsByDate($events, $gridStart, $gridEnd);
        $days = collect();

        for ($day = $gridStart; $day <= $gridEnd; $day = $day->addDay()) {
            $days->push([
                'date' => $day,
                'inMonth' => $day->month === $month->month,
                'isToday' => $day->isToday(),
                'events' => $eventsByDate->get($day->toDateString(), collect()),
            ]);
        }

        return $days->chunk(7);
    }

    /**
     * Index events by every calendar day they cover, clamped to the visible grid.
     *
     * A multi-day event appears on each day it spans rather than only on its
     * start date, so a three-day conference reads as three days on the calendar.
     * Clamping to the grid keeps the work proportional to the month on screen.
     *
     * Spans handled: entirely before the grid and entirely after it (no cells);
     * starting or ending outside it (clamped); single-day (one cell).
     *
     * @param  Collection<int, Event>  $events
     * @return Collection<string, Collection<int, Event>>
     */
    private function eventsByDate(Collection $events, CarbonImmutable $gridStart, CarbonImmutable $gridEnd): Collection
    {
        $byDate = [];

        foreach ($events as $event) {
            $firstDay = $event->start_date_time->toImmutable()->startOfDay()->max($gridStart);
            $lastDay = $event->end_date_time->toImmutable()->startOfDay()->min($gridEnd);

            for ($day = $firstDay; $day <= $lastDay; $day = $day->addDay()) {
                $byDate[$day->toDateString()][] = $event;
            }
        }

        return collect($byDate)->map(fn (array $dayEvents) => collect($dayEvents));
    }
}
