<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class TicketController (Anggota 6)
 *
 * Tiket digital = booking yang sudah dibayar (status confirmed).
 * Dipakai untuk halaman "Pesanan Saya" dan halaman detail tiket.
 */
class TicketController extends Controller
{
    private const RELATIONS = [
        'schedule.movie',
        'schedule.studio.cinema',
        'bookingSeats.scheduleSeat.seat',
        'successfulPayment',
    ];

    /**
     * GET /api/tickets
     * "Pesanan Saya": semua tiket yang sudah lunas milik user.
     */
    public function index(Request $request): JsonResponse
    {
        $tickets = Booking::with(self::RELATIONS)
            ->where('user_id', $request->user()->id)
            ->where('status', 'confirmed')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar tiket berhasil diambil.',
            'data' => TicketResource::collection($tickets),
        ]);
    }

    /**
     * GET /api/tickets/{code}
     * Detail satu tiket berdasarkan kode booking (mis. MTX-20261125-ABCD).
     * User biasa hanya bisa melihat tiket miliknya; admin bisa melihat semua.
     */
    public function show(Request $request, string $code): JsonResponse
    {
        $user = $request->user();

        $booking = Booking::with(self::RELATIONS)
            ->where('booking_code', $code)
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->first();

        if ($booking === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tiket tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail tiket berhasil diambil.',
            'data' => new TicketResource($booking),
        ]);
    }
}
