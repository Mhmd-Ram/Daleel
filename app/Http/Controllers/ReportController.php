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
     * What a repeat reporter is told. Used by both the check below and the
     * constraint that backs it up, so the two can never drift apart.
     */
    private const ALREADY_REPORTED = 'You have already reported this event. An admin will review it.';

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
            return back()->with('error', self::ALREADY_REPORTED);
        }

        $report = new Report($request->validated());
        $report->attendee_id = $request->user()->id;

        try {
            $event->reports()->save($report);
        } catch (UniqueConstraintViolationException) {
            // Two submissions in flight at once both passed the check above.
            // The index settled it; say the same thing rather than throwing.
            return back()->with('error', self::ALREADY_REPORTED);
        }

        return back()->with('success', 'Thanks. Your report has been sent to the administrators.');
    }

    /**
     * Whether this person has already flagged this event.
     */
    private function alreadyReported(Event $event, int $attendeeId): bool
    {
        return $event->reports()->where('attendee_id', $attendeeId)->exists();
    }
}
