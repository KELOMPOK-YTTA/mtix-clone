<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CinemaController extends Controller
{
    // GET /api/cinemas?city=Pontianak
    public function index(Request $request): JsonResponse
    {
        $query = Cinema::with('studios');

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Data bioskop berhasil diambil',
            'data'    => $query->get(),
        ]);
    }

    // GET /api/cinemas/{id}
    public function show(int $id): JsonResponse
    {
        $cinema = Cinema::with('studios')->find($id);

        if (!$cinema) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Bioskop tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail bioskop berhasil diambil',
            'data'    => $cinema,
        ]);
    }

    // POST /api/cinemas
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'city'    => 'required|string|max:255',
            'address' => 'required|string',
        ]);

        $cinema = Cinema::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Bioskop berhasil ditambahkan',
            'data'    => $cinema,
        ], 201);
    }

    // PUT /api/cinemas/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $cinema = Cinema::find($id);

        if (!$cinema) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Bioskop tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'name'    => 'sometimes|required|string|max:255',
            'city'    => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
        ]);

        $cinema->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Bioskop berhasil diperbarui',
            'data'    => $cinema,
        ]);
    }

    // DELETE /api/cinemas/{id}
    public function destroy(int $id): JsonResponse
    {
        $cinema = Cinema::find($id);

        if (!$cinema) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Bioskop tidak ditemukan',
            ], 404);
        }

        $cinema->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Bioskop berhasil dihapus',
        ]);
    }
}