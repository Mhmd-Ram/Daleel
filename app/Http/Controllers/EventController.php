<?php

namespace App\Http\Controllers;

use App\Enums\LibyanCity;
use App\Models\Category;
use App\Models\Event;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * The public event listing: upcoming published events, narrowed by an optional
     * keyword and the category / date / city filters from the SRS (FR-4.2 - FR-4.5).
     *
     * Every filter value is user-editable in the query string, so anything invalid is
     * ignored rather than rejected — a mangled URL must still render the page. This
     * follows RegistrationController::resolveMonth(), which does the same for ?month.
     */
    public function index(Request $request): View
    {
        $keyword = $this->keyword($request);
        $category = $request->integer('category') ?: null;
        $city = $this->city($request);
        $dateRange = $this->dateRange($request);

        $events = Event::with('category')
            ->where('is_active', true)
            ->where('end_date_time', '>', now())
            ->when($keyword, fn ($query, $term) => $query->where(function ($query) use ($term) {
                // LOWER(...) LIKE rather than ILIKE: the app runs on Postgres but the
                // test suite runs on SQLite, and ILIKE exists only on Postgres.
                $pattern = '%'.mb_strtolower($term).'%';

                $query->whereRaw('LOWER(name) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$pattern]);
            }))
            ->when($category, fn ($query, $id) => $query->where('category_id', $id))
            ->when($city, fn ($query, $value) => $query->where('city', $value->value))
            ->when($dateRange, fn ($query, $range) => $query->whereBetween('start_date_time', $range))
            ->orderBy('start_date_time')
            ->paginate(12)
            ->withQueryString();

        return view('events.index', [
            'events' => $events,
            'categories' => Category::orderBy('name')->get(),
            'cities' => LibyanCity::cases(),
            'selectedCategory' => $category,
            'selectedCity' => $city,
            'selectedDate' => $this->datePreset($request),
            'keyword' => $keyword,
            // Distinguishes "nothing matched your filters" (FR-4.4) from "no events
            // published at all", which are different messages to the reader.
            'isFiltered' => $keyword !== null || $category !== null || $city !== null || $dateRange !== null,
        ]);
    }

    /**
     * Show a single event's full details.
     */
    public function show(Event $event): View|Response
    {
        // An unpublished or removed event gets the SRS's "Event Unavailable"
        // page (FR-5.4) rather than a bare 404. The status stays 404 so
        // crawlers still read it as gone, but a human gets an explanation.
        if ($event->trashed() || ! $event->is_active) {
            return response()->view('events.unavailable', [], 404);
        }

        $event->loadCount('registeredUsers');

        $isRegistered = auth()->check()
            && $event->registeredUsers()->where('user_id', auth()->id())->exists();

        return view('events.show', [
            'event' => $event,
            'isRegistered' => $isRegistered,
        ]);
    }

    /**
     * The search term, or null when it is absent or too short to be meaningful.
     * Capped so a pathological query cannot become an expensive LIKE scan.
     */
    private function keyword(Request $request): ?string
    {
        $term = trim((string) $request->query('q', ''));

        return mb_strlen($term) >= 2 ? mb_substr($term, 0, 120) : null;
    }

    /**
     * The chosen city, or null when the value is not one of ours.
     */
    private function city(Request $request): ?LibyanCity
    {
        return LibyanCity::tryFrom((string) $request->query('city', ''));
    }

    /**
     * The date preset, echoed back to the view so the dropdown can reselect it.
     * Only a recognised preset comes back, so an invented `?date` never sticks
     * to the form after the query itself has been ignored.
     */
    private function datePreset(Request $request): string
    {
        $preset = (string) $request->query('date', '');

        return in_array($preset, ['today', 'this_week', 'this_month', 'next_month'], true) ? $preset : '';
    }

    /**
     * Turn the date preset into an inclusive [from, to] window, or null when no
     * recognised preset was given. Presets match the SRS wireframe (Figure 3.2.1.3).
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}|null
     */
    private function dateRange(Request $request): ?array
    {
        $now = CarbonImmutable::now();

        return match ($this->datePreset($request)) {
            'today' => [$now->startOfDay(), $now->endOfDay()],
            'this_week' => [$now->startOfWeek(CarbonInterface::SUNDAY), $now->endOfWeek(CarbonInterface::SATURDAY)],
            'this_month' => [$now->startOfMonth(), $now->endOfMonth()],
            'next_month' => [$now->addMonth()->startOfMonth(), $now->addMonth()->endOfMonth()],
            default => null,
        };
    }
}
