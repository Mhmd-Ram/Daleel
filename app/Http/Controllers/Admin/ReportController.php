<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The moderation queue for events attendees have flagged (SRS UC9, FR-7.4).
 */
class ReportController extends Controller
{
    /**
     * Every report, newest first, plus the worst offenders.
     *
     * An optional `?event=` narrows the queue to a single event, which is what
     * the reports badge on the event list links to: a badge that says "3
     * reports" about one event should open those three, not all of them.
     */
    public function index(Request $request): View
    {
        $event = $request->integer('event') ?: null;

        $reports = Report::with(['attendee', 'event.category', 'event.admin', 'event.organizer'])
            ->when($event, fn ($query, $id) => $query->where('event_id', $id))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.reports.index', [
            'reports' => $reports,
            'filteredEvent' => $event ? Event::find($event) : null,
            // Ranked by how many people flagged them, so the worst offender is
            // visible without reading the raw list. whereHas rather than
            // having(): HAVING on a withCount alias needs a GROUP BY on
            // Postgres, and the test suite runs on SQLite.
            'mostReported' => Event::withCount('reports')
                ->whereHas('reports')
                ->orderByDesc('reports_count')
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * Dismiss a report.
     *
     * The row is deleted rather than flagged: the design has no status column,
     * and a dismissed report carries no meaning once an admin has looked at it.
     */
    public function destroy(Report $report): RedirectResponse
    {
        $report->delete();

        return back()->with('success', __('app.flash.report_dismissed'));
    }
}
