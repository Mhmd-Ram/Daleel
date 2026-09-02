<?php

namespace App\Console\Commands;

use App\Mail\EventReminder;
use App\Models\Event;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Send the reminder email the SRS promises when someone saves an event (FR-6.5).
 *
 * Runs hourly. The `reminder_sent` pivot flag makes it idempotent, so an extra
 * run - or a run after a failed one - never sends the same person two
 * reminders. Events are only picked up once they are inside the window, so an
 * event saved two months out is not reminded about today.
 */
class SendEventReminders extends Command
{
    /**
     * The window, in hours before the start time, in which a reminder goes out.
     */
    private const LEAD_TIME_HOURS = 24;

    protected $signature = 'events:send-reminders';

    protected $description = 'Email everyone whose saved event starts within the next 24 hours';

    public function handle(): int
    {
        $events = Event::query()
            ->where('is_active', true)
            ->whereBetween('start_date_time', [now(), now()->addHours(self::LEAD_TIME_HOURS)])
            ->with(['registeredUsers' => fn ($query) => $query->wherePivot('reminder_sent', false)])
            ->get();

        $sent = 0;

        foreach ($events as $event) {
            foreach ($event->registeredUsers as $user) {
                Mail::to($user)->queue(new EventReminder($user, $event));
                $this->markReminded($user, $event);

                $sent++;
            }
        }

        $this->info("Queued {$sent} reminder(s).");

        return self::SUCCESS;
    }

    /**
     * Flag the calendar entry so a later run skips it.
     *
     * A direct query builder update rather than updateExistingPivot: the pivot
     * table has no updated_at column, and Eloquent would try to touch one.
     */
    private function markReminded(User $user, Event $event): void
    {
        DB::table('user_regestrations')
            ->where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->update(['reminder_sent' => true]);
    }
}
