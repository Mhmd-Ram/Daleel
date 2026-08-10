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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('location');
            $table->dateTime('start_date_time');
            $table->dateTime('end_date_time');
            // Spelling kept from the brief (Section 4 starter-code note).
            $table->decimal('tiket_cost', 8, 2)->default(0);
            // Optional capacity limit; nullable means "no limit".
            $table->unsignedInteger('max_capacity')->nullable();
            $table->boolean('is_active')->default(false);
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
