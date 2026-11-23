<?php

namespace App\Repositories;

use App\Contracts\ScheduleRepositoryInterface;
use App\Models\Schedule;
use Illuminate\Support\Collection;

/**
 * Class ScheduleRepository
 *
 * SOLID - Single Responsibility Principle:
 * Hanya bertugas menjalankan query database untuk jadwal tayang.
 * SOLID - Dependency Inversion Principle:
 * Mengimplementasikan ScheduleRepositoryInterface sehingga ScheduleService
 * bergantung pada abstraksi, bukan pada class konkret ini.
 */
class ScheduleRepository implements ScheduleRepositoryInterface
{
    public function search(array $filters): Collection
    {
        $query = Schedule::query()
            ->with(['movie', 'studio.cinema'])
            ->withCount('availableSeats');

        if (! empty($filters['movie_id'])) {
            $query->forMovie((int) $filters['movie_id']);
        }

        if (! empty($filters['studio_id'])) {
            $query->forStudio((int) $filters['studio_id']);
        }

        if (! empty($filters['cinema_id'])) {
            $query->forCinema((int) $filters['cinema_id']);
        }

        if (! empty($filters['city'])) {
            $query->forCity($filters['city']);
        }

        if (! empty($filters['time_from'])) {
            $query->fromTime($filters['time_from']);
        }

        // Tanpa filter tanggal → tampilkan jadwal hari ini dan seterusnya
        if (! empty($filters['date'])) {
            $query->onDate($filters['date']);
        } else {
            $query->upcoming();
        }

        return $query->orderBy('date')->orderBy('time')->get();
    }

    public function findById(int $id): ?Schedule
    {
        return Schedule::with(['movie', 'studio.cinema'])
            ->withCount('availableSeats')
            ->find($id);
    }

    public function findByStudioAndDate(int $studioId, string $date, ?int $exceptId = null): Collection
    {
        return Schedule::with('movie')
            ->forStudio($studioId)
            ->onDate($date)
            ->when($exceptId !== null, fn ($query) => $query->whereKeyNot($exceptId))
            ->get();
    }

    public function create(array $data): Schedule
    {
        return Schedule::create($data);
    }

    public function update(Schedule $schedule, array $data): Schedule
    {
        $schedule->update($data);

        return $schedule->refresh();
    }

    public function delete(Schedule $schedule): void
    {
        $schedule->delete();
    }
}
