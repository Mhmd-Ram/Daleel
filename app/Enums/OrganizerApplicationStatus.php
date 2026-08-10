<?php

namespace App\Enums;

/**
 * Where an organizer application sits in the admin review queue.
 */
enum OrganizerApplicationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
