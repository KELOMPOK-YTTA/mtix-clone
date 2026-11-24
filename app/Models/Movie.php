<?php

namespace App\Models;

use App\Models\Abstract\BaseContent;
use Database\Factories\MovieFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Movie
 *
 * INHERITANCE (OOP): Movie mewarisi semua sifat, enkapsulasi, dan method dari BaseContent.
 */
class Movie extends BaseContent
{
    /** @use HasFactory<MovieFactory> */
    use HasFactory;

    /**
     * ENKAPSULASI (OOP): Mendefinisikan kolom yang boleh diisi secara mass-assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'title',
        'synopsis',
        'genre',
        'duration_minutes',
        'format',
        'poster_url',
        'release_date',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'duration_minutes' => 'integer',
        ];
    }

    /**
     * POLIMORFISME (OOP): Implementasi konkrit method abstrak getStatusLabel() dari BaseContent.
     * Mengembalikan label status film yang mudah dibaca pengguna.
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'now_playing' => 'Sedang Tayang',
            'coming_soon' => 'Segera Tayang',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Relasi ke jadwal tayang (Schedule) — modul Anggota 4.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
