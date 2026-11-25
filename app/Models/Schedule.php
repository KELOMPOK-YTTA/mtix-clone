<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Schedule
 *
 * Jadwal tayang: satu film diputar di satu studio pada tanggal & jam tertentu.
 */
class Schedule extends Model
{
    /**
     * ENKAPSULASI (OOP): Kolom yang boleh diisi secara mass-assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'movie_id',
        'studio_id',
        'date',
        'time',
        'price',
        'format',
    ];

    /**
     * ENKAPSULASI (OOP): Sembunyikan timestamp internal dari JSON.
     *
     * @var list<string>
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'price' => 'integer',
        ];
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }

    public function scheduleSeats(): HasMany
    {
        return $this->hasMany(ScheduleSeat::class);
    }

    public function availableSeats(): HasMany
    {
        return $this->scheduleSeats()->where('status', ScheduleSeat::STATUS_AVAILABLE);
    }

    public function seats(): BelongsToMany
    {
        return $this->belongsToMany(Seat::class, 'schedule_seats')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function scopeForMovie(Builder $query, int $movieId): Builder
    {
        return $query->where('movie_id', $movieId);
    }

    public function scopeForStudio(Builder $query, int $studioId): Builder
    {
        return $query->where('studio_id', $studioId);
    }

    public function scopeForCinema(Builder $query, int $cinemaId): Builder
    {
        return $query->whereHas('studio', fn (Builder $studioQuery) => $studioQuery->where('cinema_id', $cinemaId));
    }

    public function scopeForCity(Builder $query, string $city): Builder
    {
        return $query->whereHas('studio.cinema', fn (Builder $cinemaQuery) => $cinemaQuery->where('city', $city));
    }

    public function scopeOnDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    public function scopeFromTime(Builder $query, string $time): Builder
    {
        return $query->where('time', '>=', $time);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('date', '>=', now()->toDateString());
    }

    /**
     * Waktu mulai tayang (gabungan tanggal + jam).
     */
    public function getStartsAt(): Carbon
    {
        return Carbon::parse($this->date->format('Y-m-d').' '.$this->time);
    }

    /**
     * Waktu selesai tayang = waktu mulai + durasi film.
     */
    public function getEndsAt(): Carbon
    {
        return $this->getStartsAt()->addMinutes((int) ($this->movie?->duration_minutes ?? 0));
    }

    /**
     * Jam mulai dalam format HH:MM (kolom time di database tersimpan HH:MM:SS).
     */
    public function getTimeLabel(): string
    {
        return substr((string) $this->time, 0, 5);
    }
}
