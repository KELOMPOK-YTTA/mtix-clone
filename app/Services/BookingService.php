<?php

namespace App\Services;

use App\Contracts\BookableInterface;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Schedule;
use App\Models\ScheduleSeat;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class BookingService implements BookableInterface
{
    public function lockSeats(int $scheduleId, array $seatIds, User $user): Booking
    {
        return DB::transaction(function () use ($scheduleId, $seatIds, $user) {
            $this->releaseExpiredBookings();

            $schedule = Schedule::findOrFail($scheduleId);

            // Tabel schedules tidak punya kolom status, jadi cek dari waktu tayang
            if ($schedule->getStartsAt()->isPast()) {
                throw new Exception('Jadwal tayang sudah lewat, tidak bisa dipesan.');
            }

            $seats = ScheduleSeat::where('schedule_id', $scheduleId)
                ->whereIn('id', $seatIds)
                ->lockForUpdate()
                ->get();

            if ($seats->count() !== count($seatIds)) {
                throw new Exception('Beberapa kursi yang dipilih tidak ditemukan pada jadwal ini.');
            }

            foreach ($seats as $seat) {
                if (! $seat->isAvailable()) {
                    throw new Exception("Kursi {$seat->seat->row_label}{$seat->seat->seat_number} sudah dipesan atau dikunci pengguna lain.");
                }
            }

            $subtotal = $seats->count() * $schedule->price;
            $adminFee = 3000.00;
            $totalAmount = $subtotal + $adminFee;

            $booking = Booking::create([
                'booking_code' => Booking::generateCode(),
                'user_id' => $user->id,
                'schedule_id' => $schedule->id,
                'total_seats' => $seats->count(),
                'subtotal' => $subtotal,
                'admin_fee' => $adminFee,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'expires_at' => now()->addMinutes(10),
            ]);

            foreach ($seats as $seat) {
                // 'booked' = kursi dipesan, menunggu pembayaran (lihat ScheduleSeat)
                $seat->update(['status' => ScheduleSeat::STATUS_BOOKED]);

                BookingSeat::create([
                    'booking_id' => $booking->id,
                    'schedule_seat_id' => $seat->id,
                    'price' => $schedule->price,
                ]);
            }

            return $booking->load(['schedule.movie', 'schedule.studio.cinema', 'bookingSeats.scheduleSeat.seat']);
        });
    }

    public function confirmBooking(int $bookingId, User $user): Booking
    {
        return DB::transaction(function () use ($bookingId, $user) {
            $booking = Booking::where('id', $bookingId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->status !== 'pending') {
                throw new Exception("Booking tidak dapat dikonfirmasi karena berstatus {$booking->status}.");
            }

            if ($booking->isExpired()) {
                $this->cancelBooking($booking->id, $user);
                throw new Exception('Waktu pemesanan telah habis (kedaluwarsa).');
            }

            $booking->update(['status' => 'confirmed']);

            foreach ($booking->bookingSeats as $bookingSeat) {
                // 'taken' = kursi sudah dibayar
                $bookingSeat->scheduleSeat->update(['status' => ScheduleSeat::STATUS_TAKEN]);
            }

            return $booking->load(['schedule.movie', 'schedule.studio.cinema', 'bookingSeats.scheduleSeat.seat']);
        });
    }

    public function cancelBooking(int $bookingId, User $user): Booking
    {
        return DB::transaction(function () use ($bookingId, $user) {
            $booking = Booking::where('id', $bookingId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($booking->status, ['pending', 'confirmed'])) {
                throw new Exception("Booking dengan status {$booking->status} tidak dapat dibatalkan.");
            }

            $booking->update(['status' => 'cancelled']);

            foreach ($booking->bookingSeats as $bookingSeat) {
                $bookingSeat->scheduleSeat->update(['status' => ScheduleSeat::STATUS_AVAILABLE]);
            }

            return $booking->fresh();
        });
    }

    public function releaseExpiredBookings(): int
    {
        $expiredBookings = Booking::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;

        foreach ($expiredBookings as $booking) {
            DB::transaction(function () use ($booking, &$count) {
                $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->first();
                if ($lockedBooking && $lockedBooking->status === 'pending') {
                    $lockedBooking->update(['status' => 'expired']);

                    foreach ($lockedBooking->bookingSeats as $bookingSeat) {
                        $bookingSeat->scheduleSeat->update(['status' => ScheduleSeat::STATUS_AVAILABLE]);
                    }
                    $count++;
                }
            });
        }

        return $count;
    }
}
