<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Already specified as `events.deleted_at : DATETIME «NULL»` in the M3 ERD.
     *
     * Implementing it is what makes FR-5.4 truthful: a hard-deleted event can
     * only ever answer with a bare 404, but a soft-deleted one can still show
     * the "Event Unavailable" page the SRS asks for.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
