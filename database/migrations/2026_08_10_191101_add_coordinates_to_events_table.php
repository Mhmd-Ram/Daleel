<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Coordinates are optional and sit alongside `location`, which stays the
     * human-readable address. Existing events have no pin, and the event page
     * simply omits the map for them rather than guessing a position.
     *
     * decimal(10, 7) covers both ranges (-90..90 and -180..180) at roughly
     * centimetre precision, and avoids the drift a float would introduce.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
