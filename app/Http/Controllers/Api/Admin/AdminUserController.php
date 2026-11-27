<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class AdminUserController (Anggota 6)
 *
 * Admin melihat daftar user dan mengubah role (user <-> admin).
 */
class AdminUserController extends Controller
{
    /**
     * GET /api/admin/users?search=0812&role=user
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'role' => ['nullable', 'in:user,admin'],
            'search' => ['nullable', 'string', 'max:50'],
        ]);

        $users = User::query()
            ->withCount(['bookings as tickets_bought' => fn ($query) => $query->where('status', 'confirmed')])
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(
                fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")
            ))
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar user berhasil diambil.',
            'data' => $users->getCollection()->map(fn (User $user) => array_merge(
                (new UserResource($user))->resolve($request),
                ['tickets_bought' => $user->tickets_bought],
            )),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * PATCH /api/admin/users/{id}/role   Body: { "role": "admin" }
     */
    public function updateRole(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['role' => ['required', 'in:user,admin']]);

        $user = User::find($id);

        if ($user === null) {
            return response()->json(['status' => 'error', 'message' => 'User tidak ditemukan.'], 404);
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Kamu tidak bisa mengubah role akunmu sendiri.'], 422);
        }

        $user->update(['role' => $data['role']]);

        return response()->json([
            'status' => 'success',
            'message' => "Role {$user->name} diubah menjadi {$user->getRoleLabel()}.",
            'data' => new UserResource($user),
        ]);
    }
}
