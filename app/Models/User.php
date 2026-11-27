<?php

namespace App\Models;

use App\Models\Abstract\BaseUser;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class User
 *
 * INHERITANCE (OOP): User mewarisi semua sifat, enkapsulasi, dan method dari BaseUser.
 */
class User extends BaseUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    /**
     * POLIMORFISME (OOP): Implementasi konkrit method abstrak getRoleLabel() dari BaseUser.
     * Mengembalikan label peran pengguna berdasarkan nilai kolom role.
     */
    public function getRoleLabel(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'user' => 'Member',
            default => 'Unknown',
        };
    }

    /** Relasi ke booking milik user (dipakai admin & statistik — Anggota 6). */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
