<?php

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Class AuthService
 *
 * SOLID - Single Responsibility Principle:
 * Mengisolasi business logic autentikasi (registrasi, login, token management).
 * SOLID - Dependency Inversion Principle:
 * Bergantung pada abstraksi (UserRepositoryInterface), bukan implementasi konkrit database.
 */
class AuthService implements AuthServiceInterface
{
    /**
     * Constructor injection menerapkan DIP (Dependency Inversion Principle).
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Registrasi pengguna baru dengan nomor HP dan PIN.
     *
     * @param  array{name: string, phone: string, pin: string, role?: string}  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        // Normalisasi format nomor HP (misal: 628xxx atau +628xxx menjadi 08xxx)
        $data['phone'] = $this->normalizePhone($data['phone']);

        // Default role adalah 'user' jika tidak disediakan
        if (empty($data['role'])) {
            $data['role'] = 'user';
        }

        /** @var User $user */
        $user = $this->userRepository->create($data);

        // Buat access token Sanctum untuk login otomatis setelah registrasi
        $token = $user->createToken('cinema-xxi-auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Login pengguna dengan nomor HP dan PIN.
     *
     * @return array{user: User, token: string}
     *
     * @throws ValidationException
     */
    public function login(string $phone, string $pin): array
    {
        $normalizedPhone = $this->normalizePhone($phone);

        $user = $this->userRepository->findByPhone($normalizedPhone)
            ?? $this->userRepository->findByPhone($phone);

        if (! $user || ! Hash::check($pin, $user->pin)) {
            throw ValidationException::withMessages([
                'phone' => ['Nomor HP atau PIN yang Anda masukkan salah.'],
            ]);
        }

        $token = $user->createToken('cinema-xxi-auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout pengguna dengan menghapus token yang sedang aktif.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Normalisasi format nomor handphone Indonesia ke bentuk standar 08xx.
     */
    private function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone) ?? '';

        if (str_starts_with($cleaned, '628')) {
            return '0'.substr($cleaned, 2);
        }

        return $cleaned;
    }
}
