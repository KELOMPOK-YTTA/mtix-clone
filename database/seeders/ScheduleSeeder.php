<?php

namespace Database\Seeders;

use App\Exceptions\ScheduleException;
use App\Models\Movie;
use App\Models\Studio;
use App\Services\ScheduleService;
use Illuminate\Database\Seeder;

/**
 * Class ScheduleSeeder
 *
 * Data dummy jadwal tayang untuk 3 hari ke depan.
 * Wajib dijalankan SETELAH CinemaSeeder dan MovieSeeder.
 */
class ScheduleSeeder extends Seeder
{
    /** @var list<string> */
    private const SHOW_TIMES = ['12:00', '14:45', '17:30', '20:15'];

    public function run(ScheduleService $scheduleService): void
    {
        $movies = Movie::where('status', 'now_playing')->get();
        $studios = Studio::all();

        if ($movies->isEmpty() || $studios->isEmpty()) {
            $this->command?->warn('ScheduleSeeder dilewati: belum ada film now_playing atau studio.');

            return;
        }

        $createdCount = 0;
        $skippedCount = 0;

        for ($day = 0; $day < 3; $day++) {
            $date = now()->addDays($day)->toDateString();

            foreach ($studios as $studioIndex => $studio) {
                foreach (self::SHOW_TIMES as $timeIndex => $time) {
                    // Putar film bergantian supaya tiap studio tidak memutar film yang sama terus
                    $movie = $movies[($studioIndex + $timeIndex + $day) % $movies->count()];

                    try {
                        $scheduleService->createSchedule([
                            'movie_id' => $movie->id,
                            'studio_id' => $studio->id,
                            'date' => $date,
                            'time' => $time,
                        ]);
                        $createdCount++;
                    } catch (ScheduleException) {
                        // Bentrok karena film sebelumnya terlalu panjang → lewati slot ini
                        $skippedCount++;
                    }
                }
            }
        }

        $this->command?->info("ScheduleSeeder: {$createdCount} jadwal dibuat, {$skippedCount} slot dilewati karena bentrok.");
    }
}
