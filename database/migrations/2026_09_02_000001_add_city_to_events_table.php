<?php

use App\Enums\LibyanCity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The public listing needs a city dropdown (SRS FR-4.5), and `location` is a
     * free-text street address that cannot back one. The column is nullable so
     * events created before this migration stay valid; the form requires it from
     * now on, so only legacy rows can be null.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('city')->nullable()->after('location');
        });

        // Best-effort backfill: most seeded addresses end in a city name.
        foreach (LibyanCity::cases() as $city) {
            DB::table('events')
                ->whereNull('city')
                ->where('location', 'like', '%'.$city->value.'%')
                ->update(['city' => $city->value]);
        }
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('city');
        });
    }
};
