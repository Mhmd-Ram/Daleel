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
 * Confirmation sent to a user after they register for an event.
 */
class EventRegistered extends Mailable implements ShouldQueue
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
            subject: __('app.emails.subject_saved', ['event' => $this->event->name]),
        );
    }

    /**
     * The message content.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.event-registered',
        );
    }
}
