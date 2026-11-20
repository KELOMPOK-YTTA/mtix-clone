<?php

namespace App\Http\Controllers\Api;

use App\Contracts\AuthServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class AuthController
 *
 * SOLID - Single Responsibility Principle:
 * Mengatur interaksi HTTP untuk registrasi akun baru, login nomor HP + PIN, logout, dan data akun.
 * SOLID - Dependency Inversion Principle:
 * Menginjeksi AuthServiceInterface (abstraksi) ke dalam konstruktor.
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    /**
     * POST /api/auth/register
     * Registrasi akun baru Cinema XXI (M-Tix) dengan nomor HP dan PIN.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi akun Cinema XXI berhasil.',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * POST /api/auth/login
     * Autentikasi user dengan nomor HP dan 6 digit PIN.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated('phone'),
            $request->validated('pin')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * POST /api/auth/logout
     * Invalidate token aktif (Sanctum).
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil. Token telah dicabut.',
        ], 200);
    }

    /**
     * GET /api/auth/me
     * Ambil data pengguna yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Data user berhasil diambil.',
            'data' => new UserResource($request->user()),
        ], 200);
    }
}
