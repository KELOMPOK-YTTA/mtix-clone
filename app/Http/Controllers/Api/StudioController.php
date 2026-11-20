<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Studio;
use App\Services\SeatLayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudioController extends Controller
{
    public function __construct(
        private SeatLayoutService $seatLayoutService
    ) {}

    // GET /api/cinemas/{id}/studios
    public function indexByCinema(int $cinemaId): JsonResponse
    {
        $cinema = Cinema::with('studios')->find($cinemaId);

        if (!$cinema) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Bioskop tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Daftar studio berhasil diambil',
            'data'    => $cinema->studios,
        ]);
    }

    // GET /api/studios/{id}/seats
    public function seats(int $id): JsonResponse
    {
        $studio = Studio::with(['seats' => function ($query) {
            $query->orderBy('row_label')->orderBy('seat_number');
        }])->find($id);

        if (!$studio) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Studio tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Layout kursi studio berhasil diambil',
            'data'    => [
                'studio' => [
                    'id'            => $studio->id,
                    'name'          => $studio->name,
                    'type'          => $studio->type,
                    'default_price' => $studio->getPrice(),
                    'total_rows'    => $studio->total_rows,
                    'seats_per_row' => $studio->seats_per_row,
                ],
                'seat_layout' => $studio->seats->groupBy('row_label'),
            ],
        ]);
    }

    // POST /api/cinemas/{id}/studios
    public function store(Request $request, int $cinemaId): JsonResponse
    {
        $cinema = Cinema::find($cinemaId);

        if (!$cinema) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Bioskop tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:regular,premiere,imax',
            'total_rows'    => 'required|integer|min:1|max:26',
            'seats_per_row' => 'required|integer|min:1|max:40',
            'gap_numbers'   => 'nullable|array',
            'gap_numbers.*' => 'integer|min:1',
        ]);

        $studio = $cinema->studios()->create([
            'name'          => $validated['name'],
            'type'          => $validated['type'],
            'total_rows'    => $validated['total_rows'],
            'seats_per_row' => $validated['seats_per_row'],
        ]);

        $this->seatLayoutService->generateSeats($studio, $validated['gap_numbers'] ?? []);

        return response()->json([
            'status'  => 'success',
            'message' => 'Studio dan layout kursi berhasil dibuat',
            'data'    => $studio->loadCount('seats'),
        ], 201);
    }

    // PUT /api/studios/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $studio = Studio::find($id);

        if (!$studio) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Studio tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'name'          => 'sometimes|required|string|max:255',
            'type'          => 'sometimes|required|in:regular,premiere,imax',
            'total_rows'    => 'sometimes|required|integer|min:1|max:26',
            'seats_per_row' => 'sometimes|required|integer|min:1|max:40',
            'gap_numbers'   => 'nullable|array',
            'gap_numbers.*' => 'integer|min:1',
        ]);

        $studio->update($validated);

        if (isset($validated['total_rows']) || isset($validated['seats_per_row']) || array_key_exists('gap_numbers', $validated)) {
            $this->seatLayoutService->generateSeats($studio, $validated['gap_numbers'] ?? []);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Studio berhasil diperbarui',
            'data'    => $studio,
        ]);
    }

    // DELETE /api/studios/{id}
    public function destroy(int $id): JsonResponse
    {
        $studio = Studio::find($id);

        if (!$studio) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Studio tidak ditemukan',
            ], 404);
        }

        $studio->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Studio berhasil dihapus',
        ]);
    }
}