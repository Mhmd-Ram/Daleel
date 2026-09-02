<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Attendee reports against events (SRS UC9, FR-7.1 - FR-7.4).
     *
     * No status column, matching the M3 ERD: the admin workflow is dismiss
     * (delete the row) or remove the event (the cascade takes its reports with
     * it), so a resolved report carries no meaning worth storing.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            // Named for the ERD's `attendee_id`; it points at `users` the same
            // way events.organizer_id does.
            $table->foreignId('attendee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('reason', 100);
            $table->timestamps();

            // One report per person per event, so a single user cannot flood
            // the queue by reporting the same event repeatedly.
            $table->unique(['attendee_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
