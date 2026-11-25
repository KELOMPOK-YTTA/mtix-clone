<?php

namespace App\Services;

use App\Contracts\BookableInterface;
use App\Contracts\PaymentMethodInterface;
use App\Exceptions\PaymentException;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\ScheduleSeat;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class PaymentService
 *
 * SOLID - Single Responsibility: hanya mengurus logika pembayaran.
 * SOLID - Dependency Inversion: bergantung pada interface (BookableInterface,
 * PaymentMethodInterface), bukan class konkret.
 */
class PaymentService
{
    /** @var array<string, PaymentMethodInterface> */
    private array $methods = [];

    /**
     * @param  iterable<PaymentMethodInterface>  $methods  didaftarkan di AppServiceProvider
     */
    public function __construct(
        private readonly BookableInterface $bookingService,
        iterable $methods,
    ) {
        foreach ($methods as $method) {
            $this->methods[$method->getCode()] = $method;
        }
    }

    /**
     * Daftar metode pembayaran + provider-nya (untuk ditampilkan di halaman bayar).
     */
    public function availableMethods(): Collection
    {
        return collect($this->methods)->map(fn (PaymentMethodInterface $method) => [
            'code' => $method->getCode(),
            'label' => $method->getLabel(),
            'providers' => $method->getProviders(),
        ])->values();
    }

    /**
     * Membayar booking milik $user.
     * Alur: validasi -> proses metode bayar -> simpan payment -> booking confirmed -> kursi taken.
     *
     * @throws PaymentException
     */
    public function pay(User $user, int $bookingId, string $methodCode, string $provider): Payment
    {
        $method = $this->resolveMethod($methodCode);

        if (! in_array($provider, $method->getProviders(), true)) {
            throw new PaymentException("Provider '{$provider}' tidak tersedia untuk metode {$method->getLabel()}.");
        }

        // Lepas dulu booking lain yang sudah lewat batas waktu (kursinya kembali available)
        $this->bookingService->releaseExpiredBookings();

        $payment = DB::transaction(function () use ($user, $bookingId, $method, $provider): Payment {
            // lockForUpdate: cegah booking yang sama dibayar dua kali bersamaan
            $booking = Booking::where('id', $bookingId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($booking === null) {
                throw new PaymentException('Booking tidak ditemukan.');
            }

            $this->ensurePayable($booking);

            $result = $method->process($booking, $provider);

            $payment = Payment::create([
                'payment_code' => Payment::generateCode(),
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'method' => $method->getCode(),
                'provider' => $provider,
                'reference' => $result['reference'],
                'amount' => $booking->total_amount,   // nominal diambil dari server, bukan dari client
                'status' => $result['success'] ? Payment::STATUS_SUCCESS : Payment::STATUS_FAILED,
                'paid_at' => $result['success'] ? now() : null,
            ]);

            if (! $result['success']) {
                return $payment;
            }

            // Booking lunas -> status confirmed, kursi berubah dari booked menjadi taken
            $booking->update(['status' => 'confirmed']);

            ScheduleSeat::whereIn('id', $booking->bookingSeats()->pluck('schedule_seat_id'))
                ->update(['status' => ScheduleSeat::STATUS_TAKEN]);

            return $payment;
        });

        if (! $payment->isSuccessful()) {
            throw new PaymentException('Pembayaran gagal diproses. Silakan coba lagi.');
        }

        return $payment->load('booking');
    }

    /**
     * Riwayat pembayaran milik user.
     */
    public function historyFor(User $user): Collection
    {
        return Payment::with('booking.schedule.movie')
            ->where('user_id', $user->id)
            ->latest()
            ->get();
    }

    /**
     * Tandai pembayaran sebagai refunded (dipakai saat admin membatalkan booking yang sudah dibayar).
     */
    public function refundForBooking(Booking $booking): void
    {
        $booking->payments()
            ->where('status', Payment::STATUS_SUCCESS)
            ->update(['status' => Payment::STATUS_REFUNDED]);
    }

    /**
     * @throws PaymentException
     */
    private function resolveMethod(string $code): PaymentMethodInterface
    {
        return $this->methods[$code]
            ?? throw new PaymentException("Metode pembayaran '{$code}' tidak dikenal.");
    }

    /**
     * @throws PaymentException
     */
    private function ensurePayable(Booking $booking): void
    {
        if ($booking->status === 'confirmed') {
            throw new PaymentException('Booking ini sudah dibayar.');
        }

        if ($booking->status !== 'pending') {
            throw new PaymentException("Booking berstatus {$booking->status}, tidak bisa dibayar.");
        }

        if ($booking->isExpired()) {
            throw new PaymentException('Batas waktu pembayaran sudah habis. Silakan pesan ulang.');
        }
    }
}
