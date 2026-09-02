<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An admin's block on an account (SRS FR-1.5, UC13). Matches the M3 ERD's
     * `is_banned : BOOLEAN «NOT NULL, DEFAULT 0»`, so existing rows default to
     * not banned and no backfill is needed.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_banned')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_banned');
        });
    }
};
