<?php

namespace App\Contracts;

use App\Models\User;

/**
 * Interface UserRepositoryInterface
 *
 * SOLID - Dependency Inversion Principle & Interface Segregation Principle:
 * Mengabstraksi akses dan manipulasi data entitas User pada database.
 */
interface UserRepositoryInterface
{
    /**
     * Simpan user baru ke database.
     *
     * @param  array{name: string, phone: string, pin: string, role?: string}  $data
     */
    public function create(array $data): User;

    /**
     * Cari user berdasarkan nomor handphone.
     */
    public function findByPhone(string $phone): ?User;

    /**
     * Cari user berdasarkan primary key ID.
     */
    public function findById(int $id): ?User;

    /**
     * Perbarui data user tertentu.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): bool;
}
