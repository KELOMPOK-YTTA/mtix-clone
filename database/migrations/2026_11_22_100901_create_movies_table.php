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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('synopsis');
            $table->string('genre');
            $table->unsignedSmallInteger('duration_minutes');
            $table->enum('format', ['2D', '3D', 'IMAX'])->default('2D');
            $table->string('poster_url')->nullable();
            $table->date('release_date');
            $table->enum('status', ['now_playing', 'coming_soon'])->default('coming_soon');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
