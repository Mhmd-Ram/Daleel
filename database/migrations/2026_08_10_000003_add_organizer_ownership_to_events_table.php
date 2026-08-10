<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * An event is now owned by either the admin who created it or the
     * organizer who created it — exactly one of the two columns is set.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->change();
            $table->foreignId('organizer_id')->nullable()->after('admin_id')
                ->constrained('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Organizer-owned events have no admin to fall back to, so they cannot
        // survive the rollback. They have to go before `admin_id` is NOT NULL again.
        DB::table('events')->whereNull('admin_id')->delete();

        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organizer_id');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable(false)->change();
        });
    }
};
