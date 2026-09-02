<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The nudge sent shortly before an event someone saved (SRS FR-6.5).
 */
class EventReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Event $event,
    ) {}

    /**
     * The message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('app.emails.subject_reminder', ['event' => $this->event->name]),
        );
    }

    /**
     * The message content.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.event-reminder',
        );
    }
}
