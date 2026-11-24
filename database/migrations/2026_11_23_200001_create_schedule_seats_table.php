<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Status kursi per jadwal. Diisi otomatis oleh ScheduleService
 * saat jadwal dibuat (salinan semua kursi studio, kecuali lorong).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->foreignId('seat_id')->constrained('seats')->cascadeOnDelete();
            $table->enum('status', ['available', 'booked', 'taken'])->default('available');
            $table->timestamps();

            $table->unique(['schedule_id', 'seat_id']);
            $table->index(['schedule_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_seats');
    }
};
