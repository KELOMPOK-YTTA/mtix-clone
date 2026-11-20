<?php

namespace App\Http\Controllers\Api;

use App\Contracts\UserRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Class UserController
 *
 * SOLID - Single Responsibility Principle:
 * Mengatur interaksi HTTP untuk melihat dan mengubah profil user.
 * SOLID - Dependency Inversion Principle:
 * Menginjeksi UserRepositoryInterface untuk operasi pembaruan data.
 */
class UserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * GET /api/user
     * Menampilkan profil user yang terautentikasi.
     */
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'message' => 'Profil pengguna berhasil diambil.',
            'data' => new UserResource($user),
        ], 200);
    }

    /**
     * PUT|PATCH /api/user
     * Memperbarui informasi profil pengguna (nama, nomor HP, atau PIN).
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $updateData = [];

        if (! empty($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }

        if (! empty($validated['phone'])) {
            $updateData['phone'] = $validated['phone'];
        }

        if (! empty($validated['new_pin'])) {
            if (! Hash::check($validated['current_pin'], $user->pin)) {
                throw ValidationException::withMessages([
                    'current_pin' => ['PIN saat ini yang Anda masukkan tidak sesuai.'],
                ]);
            }
            $updateData['pin'] = $validated['new_pin'];
        }

        $this->userRepository->update($user, $updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil pengguna berhasil diperbarui.',
            'data' => new UserResource($user->fresh()),
        ], 200);
    }
}
