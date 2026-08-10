<?php

namespace App\Enums;

/**
 * What a user is allowed to do beyond attending events.
 */
enum UserRole: string
{
    case Attendee = 'attendee';
    case Organizer = 'organizer';
}
