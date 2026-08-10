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

    /**
     * A human label for the admin queue.
     */
    public function label(): string
    {
        return ucfirst($this->value);
    }
}
