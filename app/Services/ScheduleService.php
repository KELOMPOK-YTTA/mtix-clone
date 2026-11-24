<?php

namespace App\Services;

use App\Contracts\ScheduleRepositoryInterface;
use App\Exceptions\ScheduleException;
use App\Models\Movie;
use App\Models\Schedule;
use App\Models\ScheduleSeat;
use App\Models\Seat;
use App\Models\Studio;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class ScheduleService
 *
 * SOLID - Single Responsibility Principle:
 * Menampung seluruh aturan bisnis jadwal tayang (cek bentrok, salin kursi,
 * aturan ubah/hapus). Controller hanya mengurus request & response.
 * SOLID - Dependency Inversion Principle:
 * Bergantung pada ScheduleRepositoryInterface, bukan class repository konkret.
 */
class ScheduleService
{
    /** Jeda bersih-bersih studio antar penayangan (menit). */
    public const CLEANING_BREAK_MINUTES = 15;

    public function __construct(
        private readonly ScheduleRepositoryInterface $scheduleRepository
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Schedule>
     */
    public function getSchedules(array $filters): Collection
    {
        return $this->scheduleRepository->search($filters);
    }

    public function findById(int $id): ?Schedule
    {
        return $this->scheduleRepository->findById($id);
    }

    /**
     * Buat jadwal baru lalu salin semua kursi studio ke schedule_seats.
     * Dibungkus transaction: kalau salin kursi gagal, jadwal ikut dibatalkan.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ScheduleException
     */
    public function createSchedule(array $data): Schedule
    {
        $movie = Movie::findOrFail($data['movie_id']);
        $studio = Studio::findOrFail($data['studio_id']);

        // Format default ikut film, harga default ikut tipe studio (Studio::getPrice dari Anggota 3)
        $data['format'] ??= $movie->format;
        $data['price'] ??= $studio->getPrice();

        $this->ensureNoConflict($movie, $studio->id, $data['date'], $data['time']);

        $schedule = DB::transaction(function () use ($data): Schedule {
            $schedule = $this->scheduleRepository->create($data);
            $this->generateScheduleSeats($schedule);

            return $schedule;
        });

        return $this->scheduleRepository->findById($schedule->id);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ScheduleException
     */
    public function updateSchedule(Schedule $schedule, array $data): Schedule
    {
        $isStudioChanged = isset($data['studio_id']) && (int) $data['studio_id'] !== $schedule->studio_id;
        $isMovieChanged = isset($data['movie_id']) && (int) $data['movie_id'] !== $schedule->movie_id;
        $isDateChanged = isset($data['date']) && $data['date'] !== $schedule->date->format('Y-m-d');
        $isTimeChanged = isset($data['time']) && $data['time'] !== $schedule->getTimeLabel();

        $isCoreFieldChanged = $isStudioChanged || $isMovieChanged || $isDateChanged || $isTimeChanged;

        // Mengubah film/studio/jam setelah ada pemesanan akan merusak tiket pembeli
        if ($isCoreFieldChanged && $this->hasBookedSeats($schedule)) {
            throw new ScheduleException(
                'Film, studio, tanggal, atau jam tidak bisa diubah karena sudah ada kursi yang dipesan. Yang masih bisa diubah: harga dan format.'
            );
        }

        if ($isCoreFieldChanged) {
            $movie = Movie::findOrFail($data['movie_id'] ?? $schedule->movie_id);

            $this->ensureNoConflict(
                $movie,
                (int) ($data['studio_id'] ?? $schedule->studio_id),
                $data['date'] ?? $schedule->date->format('Y-m-d'),
                $data['time'] ?? $schedule->getTimeLabel(),
                $schedule->id,
            );
        }

        DB::transaction(function () use ($schedule, $data, $isStudioChanged): void {
            $this->scheduleRepository->update($schedule, $data);

            if ($isStudioChanged) {
                $schedule->scheduleSeats()->delete();
                $this->generateScheduleSeats($schedule);
            }
        });

        return $this->scheduleRepository->findById($schedule->id);
    }

    /**
     * @throws ScheduleException
     */
    public function deleteSchedule(Schedule $schedule): void
    {
        if ($this->hasBookedSeats($schedule)) {
            throw new ScheduleException('Jadwal tidak bisa dihapus karena sudah ada kursi yang dipesan.');
        }

        // Baris schedule_seats ikut terhapus otomatis (cascadeOnDelete)
        $this->scheduleRepository->delete($schedule);
    }

    /**
     * Layout kursi beserta statusnya untuk satu jadwal, dikelompokkan per baris.
     *
     * @return Collection<int, array{row: string, seats: Collection<int, array{seat_id: int, label: string, status: string}>}>
     */
    public function getSeatMap(Schedule $schedule): Collection
    {
        return $schedule->scheduleSeats()
            ->with('seat')
            ->get()
            ->sortBy([
                fn (ScheduleSeat $a, ScheduleSeat $b) => strcmp($a->seat->row_label, $b->seat->row_label),
                fn (ScheduleSeat $a, ScheduleSeat $b) => $a->seat->seat_number <=> $b->seat->seat_number,
            ])
            ->groupBy(fn (ScheduleSeat $scheduleSeat) => $scheduleSeat->seat->row_label)
            ->map(fn (Collection $seats, string $rowLabel) => [
                'row' => $rowLabel,
                'seats' => $seats->map(fn (ScheduleSeat $scheduleSeat) => [
                    'seat_id' => $scheduleSeat->seat_id,
                    'label' => $scheduleSeat->seat->row_label.$scheduleSeat->seat->seat_number,
                    'status' => $scheduleSeat->status,
                ])->values(),
            ])
            ->values();
    }

    /**
     * Tolak jadwal jika rentang [mulai, selesai + jeda] bertabrakan dengan
     * jadwal lain di studio dan tanggal yang sama.
     *
     * @throws ScheduleException
     */
    private function ensureNoConflict(Movie $movie, int $studioId, string $date, string $time, ?int $exceptId = null): void
    {
        $newStart = Carbon::parse("{$date} {$time}");
        $newEnd = $newStart->copy()->addMinutes($movie->duration_minutes + self::CLEANING_BREAK_MINUTES);

        foreach ($this->scheduleRepository->findByStudioAndDate($studioId, $date, $exceptId) as $otherSchedule) {
            $otherStart = $otherSchedule->getStartsAt();
            $otherEnd = $otherSchedule->getEndsAt()->addMinutes(self::CLEANING_BREAK_MINUTES);

            if ($newStart->lt($otherEnd) && $otherStart->lt($newEnd)) {
                throw ScheduleException::conflict(
                    $otherSchedule->movie?->title ?? 'film lain',
                    $otherStart->format('H:i'),
                    $otherSchedule->getEndsAt()->format('H:i'),
                );
            }
        }
    }

    /**
     * Salin semua kursi fisik studio (bukan lorong / is_gap) ke schedule_seats.
     *
     * @throws ScheduleException
     */
    private function generateScheduleSeats(Schedule $schedule): void
    {
        $now = now();

        $rows = Seat::where('studio_id', $schedule->studio_id)
            ->where('is_gap', false)
            ->pluck('id')
            ->map(fn (int $seatId) => [
                'schedule_id' => $schedule->id,
                'seat_id' => $seatId,
                'status' => ScheduleSeat::STATUS_AVAILABLE,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

        if ($rows->isEmpty()) {
            throw new ScheduleException('Studio ini belum punya layout kursi. Buat layout kursi studio terlebih dahulu.');
        }

        foreach ($rows->chunk(500) as $chunk) {
            ScheduleSeat::insert($chunk->values()->all());
        }
    }

    private function hasBookedSeats(Schedule $schedule): bool
    {
        return $schedule->scheduleSeats()
            ->where('status', '!=', ScheduleSeat::STATUS_AVAILABLE)
            ->exists();
    }
}
