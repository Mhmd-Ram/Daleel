<?php

namespace App\Enums;

/**
 * Why an attendee flagged an event for review.
 */
enum ReportReason: string
{
    case FakeLocation = 'fake_location';
    case InappropriateContent = 'inappropriate_content';
    case MisleadingDescription = 'misleading_description';
    case Spam = 'spam';
    case Other = 'other';

    /**
     * The human label shown in the report form and the admin queue. The stored
     * value is a stable key, so the wording can change without a migration.
     */
    public function label(): string
    {
        return match ($this) {
            self::FakeLocation => 'Fake or wrong location',
            self::InappropriateContent => 'Inappropriate content',
            self::MisleadingDescription => 'Misleading description',
            self::Spam => 'Spam',
            self::Other => 'Something else',
        };
    }
}
