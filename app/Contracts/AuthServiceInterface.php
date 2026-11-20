<?php

namespace App\Contracts;

use App\Models\User;

/**
 * Interface AuthServiceInterface
 *
 * SOLID - Interface Segregation Principle & Dependency Inversion Principle:
 * Kontrak logika bisnis autentikasi akun Cinema XXI.
 */
interface AuthServiceInterface
{
    /**
     * Registrasi pengguna baru dan berikan access token.
     *
     * @param  array{name: string, phone: string, pin: string, role?: string}  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array;

    /**
     * Autentikasi user menggunakan nomor HP dan PIN.
     *
     * @return array{user: User, token: string}
     */
    public function login(string $phone, string $pin): array;

    /**
     * Cabut / hapus token aktif pengguna saat logout.
     */
    public function logout(User $user): void;
}
