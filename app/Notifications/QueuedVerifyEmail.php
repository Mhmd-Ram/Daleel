<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Laravel's verification email, moved onto the queue.
 *
 * The stock notification sends inline. Against a local Mailpit sink that is
 * instant, but against Gmail it puts an SMTP round-trip inside the registration
 * request - slow at best, and a 500 on the user's first action if Gmail is
 * unreachable. Queuing it makes all three of the app's emails behave alike.
 */
class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;
}
