<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Matches `calendar_entries.reminder_sent` in the M3 ERD. The flag is what
     * makes the reminder job idempotent: it can run every hour without ever
     * sending the same person two reminders for the same event.
     */
    public function up(): void
    {
        Schema::table('user_regestrations', function (Blueprint $table) {
            $table->boolean('reminder_sent')->default(false)->after('event_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_regestrations', function (Blueprint $table) {
            $table->dropColumn('reminder_sent');
        });
    }
};
