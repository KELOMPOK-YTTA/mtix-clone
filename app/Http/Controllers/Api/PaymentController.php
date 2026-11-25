<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\PaymentException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\TicketResource;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class PaymentController (Anggota 6)
 *
 * SOLID - Single Responsibility: controller hanya menerima request & mengembalikan response,
 * semua logika ada di PaymentService.
 */
class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    /**
     * GET /api/payments/methods
     * Daftar metode pembayaran yang tersedia.
     */
    public function methods(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Daftar metode pembayaran berhasil diambil.',
            'data' => $this->paymentService->availableMethods(),
        ]);
    }

    /**
     * GET /api/payments
     * Riwayat pembayaran milik user yang login.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Riwayat pembayaran berhasil diambil.',
            'data' => PaymentResource::collection($this->paymentService->historyFor($request->user())),
        ]);
    }

    /**
     * POST /api/payments
     * Body: { "booking_id": 1, "method": "ewallet", "provider": "gopay" }
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $payment = $this->paymentService->pay(
                $request->user(),
                (int) $data['booking_id'],
                $data['method'],
                $data['provider'],
            );
        } catch (PaymentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }

        $booking = $payment->booking->load([
            'schedule.movie',
            'schedule.studio.cinema',
            'bookingSeats.scheduleSeat.seat',
            'successfulPayment',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pembayaran berhasil. Tiket kamu sudah terbit.',
            'data' => [
                'payment' => new PaymentResource($payment),
                'ticket' => new TicketResource($booking),
            ],
        ], 201);
    }
}
