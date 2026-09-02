<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Event;
use App\Models\Report;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
{
    /**
     * File a report against an event (SRS UC9, FR-7.1 - FR-7.3).
     *
     * A repeat report from the same person is answered with a friendly message
     * rather than an error: telling someone "you already reported this" is the
     * honest response, and the unique index means the row cannot exist twice
     * either way.
     */
    public function store(StoreReportRequest $request, Event $event): RedirectResponse
    {
        abort_unless($event->is_active, 404);

        if ($this->alreadyReported($event, $request->user()->id)) {
            return back()->with('error', __('app.flash.already_reported'));
        }

        $report = new Report($request->validated());
        $report->attendee_id = $request->user()->id;

        try {
            $event->reports()->save($report);
        } catch (UniqueConstraintViolationException) {
            // Two submissions in flight at once both passed the check above.
            // The index settled it; say the same thing rather than throwing.
            return back()->with('error', __('app.flash.already_reported'));
        }

        return back()->with('success', __('app.flash.report_sent'));
    }

    /**
     * Whether this person has already flagged this event.
     */
    private function alreadyReported(Event $event, int $attendeeId): bool
    {
        return $event->reports()->where('attendee_id', $attendeeId)->exists();
    }
}
