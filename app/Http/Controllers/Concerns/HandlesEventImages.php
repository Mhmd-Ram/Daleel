<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Cover-image handling shared by the admin and organizer event controllers.
 *
 * Both sides offer the same field and must treat it identically; without this
 * the two copies would drift, and the one that drifted would leak files.
 */
trait HandlesEventImages
{
    /**
     * Apply the request's cover-image intent to the event.
     *
     * Call before saving. Does nothing when the file input was left alone,
     * which is what preserves the existing cover on an ordinary edit.
     */
    protected function syncImage(Event $event, Request $request): void
    {
        if ($request->boolean('remove_image')) {
            $this->forgetImage($event);
        }

        if ($file = $request->file('image')) {
            // Drop the outgoing file first: a replaced cover is otherwise
            // orphaned on disk with nothing left pointing at it.
            $this->forgetImage($event);

            // The `public` disk by name, never the default - FILESYSTEM_DISK is
            // `local`, which writes to storage/app/private and is unreachable
            // from the web.
            $event->image_path = $file->store('events', 'public');
        }
    }

    /**
     * Delete the event's cover from disk and clear the column.
     */
    private function forgetImage(Event $event): void
    {
        if ($event->image_path) {
            Storage::disk('public')->delete($event->image_path);
            $event->image_path = null;
        }
    }
}
