<?php

namespace App\Models;

use App\Models\Abstract\BaseUser;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}
