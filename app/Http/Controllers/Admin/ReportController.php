<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * The moderation queue for events attendees have flagged (SRS UC9, FR-7.4).
 */
class ReportController extends Controller
{
    /**
     * Every report, newest first, plus the worst offenders.
     */
    public function index(): View
    {
        $reports = Report::with(['attendee', 'event.category', 'event.admin', 'event.organizer'])
            ->latest()
            ->paginate(25);

        return view('admin.reports.index', [
            'reports' => $reports,
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
