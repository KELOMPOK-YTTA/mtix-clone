<?php

namespace App\Http\Controllers\Api;

use App\Contracts\BookableInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private BookableInterface $bookingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::with(['schedule.movie', 'schedule.studio.cinema', 'bookingSeats.scheduleSeat.seat'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar riwayat pemesanan berhasil diambil.',
            'data' => BookingResource::collection($bookings),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schedule_id' => ['required', 'integer', 'exists:schedules,id'],
            'seat_ids' => ['required', 'array', 'min:1', 'max:6'],
            'seat_ids.*' => ['required', 'integer', 'distinct', 'exists:schedule_seats,id'],
        ]);

        try {
            $booking = $this->bookingService->lockSeats(
                $validated['schedule_id'],
                $validated['seat_ids'],
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Kursi berhasil dikunci. Selesaikan pembayaran dalam 10 menit.',
                'data' => new BookingResource($booking),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $booking = Booking::with(['schedule.movie', 'schedule.studio.cinema', 'bookingSeats.scheduleSeat.seat'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail pemesanan berhasil diambil.',
            'data' => new BookingResource($booking),
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->cancelBooking($id, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Pemesanan berhasil dibatalkan dan kursi telah dilepaskan.',
                'data' => new BookingResource($booking),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
