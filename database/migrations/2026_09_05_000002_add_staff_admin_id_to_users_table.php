<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Links an admin to the ordinary user account they browse the public
            // site with. Admins sign in on their own guard against the `admins`
            // table and have no user row, so without this they are a guest on
            // the site and cannot register for anything.
            //
            // Named `staff_admin_id`, not `admin_id`: `events.admin_id` already
            // means "created by this admin", which is a different relationship.
            //
            // The column lives on `users` rather than `admins` so that keeping
            // these synthetic accounts out of the admin's own user list and
            // counts is a single `whereNull` - see User::scopeReal().
            $table->foreignId('staff_admin_id')
                ->nullable()
                ->unique()
                ->after('id')
                ->constrained('admins')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('staff_admin_id');
        });
    }
};
