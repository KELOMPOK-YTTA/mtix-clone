<?php

namespace App\Contracts;

use App\Models\Schedule;
use Illuminate\Support\Collection;

/**
 * Interface ScheduleRepositoryInterface
 *
 * SOLID - Interface Segregation Principle:
 * Kontrak khusus untuk akses data jadwal tayang (Anggota 4).
 */
interface ScheduleRepositoryInterface
{
    /**
     * Ambil daftar jadwal dengan filter opsional.
     *
     * @param  array{movie_id?: int, cinema_id?: int, studio_id?: int, city?: string, date?: string, time_from?: string}  $filters
     * @return Collection<int, Schedule>
     */
    public function search(array $filters): Collection;

    public function findById(int $id): ?Schedule;

    /**
     * Semua jadwal di satu studio pada satu tanggal (dipakai untuk cek bentrok).
     *
     * @return Collection<int, Schedule>
     */
    public function findByStudioAndDate(int $studioId, string $date, ?int $exceptId = null): Collection;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Schedule;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Schedule $schedule, array $data): Schedule;

    public function delete(Schedule $schedule): void;
}
