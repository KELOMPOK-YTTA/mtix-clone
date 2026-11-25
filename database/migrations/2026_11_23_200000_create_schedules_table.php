<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel jadwal tayang.
 * PENTING: timestamp nama file ini harus LEBIH BARU dari migration
 * movies, cinemas, studios, dan seats, karena tabel ini punya FK ke sana.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained('movies')->cascadeOnDelete();
            $table->foreignId('studio_id')->constrained('studios')->cascadeOnDelete();
            $table->date('date');
            $table->time('time');
            $table->unsignedInteger('price');           // dalam Rupiah, contoh 45000
            $table->string('format', 10)->default('2D'); // 2D / 3D / IMAX
            $table->timestamps();

            // Satu studio tidak boleh punya dua jadwal di jam mulai yang sama
            $table->unique(['studio_id', 'date', 'time']);
            // Mempercepat filter "jadwal film X pada tanggal Y"
            $table->index(['movie_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
