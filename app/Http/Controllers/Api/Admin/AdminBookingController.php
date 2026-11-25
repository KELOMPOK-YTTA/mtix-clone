<?php

namespace App\Http\Controllers\Api\Admin;

use App\Contracts\BookableInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\TicketResource;
use App\Models\Booking;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class AdminBookingController (Anggota 6)
 *
 * Admin melihat & mengelola SEMUA booking.
 * SOLID - Dependency Inversion: memakai ulang BookingService (lewat BookableInterface)
 * milik Anggota 5, tidak menulis ulang logika pembatalan.
 */
class AdminBookingController extends Controller
{
    private const RELATIONS = [
        'user',
        'schedule.movie',
        'schedule.studio.cinema',
        'bookingSeats.scheduleSeat.seat',
        'successfulPayment',
    ];

    public function __construct(
        private readonly BookableInterface $bookingService,
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * GET /api/admin/bookings?status=confirmed&date=2026-11-27&search=MTX-
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,confirmed,cancelled,expired'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'search' => ['nullable', 'string', 'max:50'],
        ]);

        $bookings = Booking::with(self::RELATIONS)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('created_at', $date))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('booking_code', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar booking berhasil diambil.',
            'data' => $bookings->getCollection()->map(fn (Booking $booking) => array_merge(
                (new BookingResource($booking))->resolve($request),
                ['user' => ['id' => $booking->user?->id, 'name' => $booking->user?->name, 'phone' => $booking->user?->phone]],
            )),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    /**
     * GET /api/admin/bookings/{id}
     */
    public function show(int $id): JsonResponse
    {
        $booking = Booking::with(self::RELATIONS)->find($id);

        if ($booking === null) {
            return response()->json(['status' => 'error', 'message' => 'Booking tidak ditemukan.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail booking berhasil diambil.',
            'data' => array_merge(
                (new TicketResource($booking))->resolve(),
                ['id' => $booking->id, 'user' => ['id' => $booking->user?->id, 'name' => $booking->user?->name, 'phone' => $booking->user?->phone]],
            ),
        ]);
    }

    /**
     * POST /api/admin/bookings/{id}/cancel
     * Membatalkan booking user. Kalau sudah dibayar, pembayaran ditandai refunded.
     */
    public function cancel(int $id): JsonResponse
    {
        $booking = Booking::with('user')->find($id);

        if ($booking === null) {
            return response()->json(['status' => 'error', 'message' => 'Booking tidak ditemukan.'], 404);
        }

        try {
            $booking = DB::transaction(function () use ($booking) {
                $cancelled = $this->bookingService->cancelBooking($booking->id, $booking->user);
                $this->paymentService->refundForBooking($cancelled);

                return $cancelled;
            });
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Booking berhasil dibatalkan dan kursi dikembalikan.',
            'data' => new BookingResource($booking),
        ]);
    }
}
