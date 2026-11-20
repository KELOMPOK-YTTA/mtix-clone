<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;

/**
 * Class UserRepository
 *
 * SOLID - Single Responsibility Principle:
 * Bertanggung jawab penuh hanya untuk operasi database entitas User.
 * SOLID - Dependency Inversion Principle:
 * Mengimplementasikan UserRepositoryInterface.
 */
class UserRepository implements UserRepositoryInterface
{
    /**
     * Simpan user baru ke database.
     *
     * @param  array{name: string, phone: string, pin: string, role?: string}  $data
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Cari user berdasarkan nomor handphone.
     */
    public function findByPhone(string $phone): ?User
    {
        return User::where('phone', $phone)->first();
    }

    /**
     * Cari user berdasarkan primary key ID.
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Perbarui data user.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }
}
