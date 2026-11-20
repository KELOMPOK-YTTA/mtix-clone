<?php

namespace App\Models\Abstract;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class BaseUser
 *
 * ABSTRAKSI (OOP): Class abstrak ini bertindak sebagai fondasi entitas User dalam sistem Cinema XXI.
 * Tidak dapat di-instantiate secara langsung dan mendefinisikan method abstrak yang wajib
 * diimplementasikan oleh class anak, serta mengisolasi properti melalui enkapsulasi.
 */
abstract class BaseUser extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * ENKAPSULASI (OOP): Properti protected menentukan atribut mana yang boleh
     * diisi secara mass-assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'pin',
        'role',
    ];

    /**
     * Default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'user',
    ];

    /**
     * ENKAPSULASI (OOP): Properti sensitif yang disembunyikan dari serialisasi JSON / array.
     *
     * @var list<string>
     */
    protected $hidden = [
        'pin',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pin' => 'hashed',
        ];
    }

    /**
     * POLIMORFISME (OOP): Method abstrak yang mewajibkan class turunan menyediakan
     * representasi label teks untuk peran (role) pengguna.
     */
    abstract public function getRoleLabel(): string;

    /**
     * Cek apakah pengguna memiliki hak akses sebagai admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Format tampilan nomor HP (misal: 0812-3456-7890).
     */
    public function getFormattedPhone(): string
    {
        $phone = $this->phone;
        if (strlen($phone) >= 10) {
            return substr($phone, 0, 4).'-'.substr($phone, 4, 4).'-'.substr($phone, 8);
        }

        return $phone;
    }

    /**
     * Tentukan nama kolom password yang digunakan oleh sistem autentikasi Laravel.
     */
    public function getAuthPasswordName(): string
    {
        return 'pin';
    }
}
