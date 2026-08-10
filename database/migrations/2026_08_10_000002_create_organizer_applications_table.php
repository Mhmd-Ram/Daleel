<?php

use App\Enums\OrganizerApplicationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organizer_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default(OrganizerApplicationStatus::Pending->value)->index();
            // The applicant's pitch to the admins.
            $table->text('message');
            // Reviews are done from the admin area, so the reviewer is an admin.
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // One *open* application per user; rejected users may apply again.
        // Partial indexes are supported by both SQLite and PostgreSQL, and the
        // Blueprint builder cannot express the WHERE clause.
        DB::statement(
            'CREATE UNIQUE INDEX organizer_applications_one_pending_per_user
             ON organizer_applications (user_id)
             WHERE status = \''.OrganizerApplicationStatus::Pending->value.'\''
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizer_applications');
    }
};
