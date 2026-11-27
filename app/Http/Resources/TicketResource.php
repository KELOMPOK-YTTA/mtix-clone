<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Format JSON tiket digital (dari booking yang sudah dibayar).
 * Field "qr_payload" cukup dijadikan QR code di frontend (mis. pakai library qrcode.js).
 *
 * @mixin \App\Models\Booking
 */
class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $schedule = $this->schedule;
        $studio = $schedule?->studio;

        return [
            'booking_code' => $this->booking_code,
            'qr_payload' => $this->booking_code,
            'status' => $this->status,
            'status_label' => match ($this->status) {
                'confirmed' => 'Lunas — tunjukkan tiket ini di bioskop',
                'pending' => 'Menunggu pembayaran',
                'cancelled' => 'Dibatalkan',
                'expired' => 'Kedaluwarsa',
                default => $this->status,
            },
            'movie' => [
                'title' => $schedule?->movie?->title,
                'poster_url' => $schedule?->movie?->poster_url,
                'duration' => $schedule?->movie?->getDurationFormatted(),
            ],
            'cinema' => $studio?->cinema?->name,
            'city' => $studio?->cinema?->city,
            'studio' => $studio?->name,
            'format' => $schedule?->format,
            'date' => $schedule?->date?->format('Y-m-d'),
            'time' => $schedule?->getTimeLabel(),
            'seats' => $this->bookingSeats
                ->map(fn ($bookingSeat) => $bookingSeat->scheduleSeat?->seat
                    ? $bookingSeat->scheduleSeat->seat->row_label.$bookingSeat->scheduleSeat->seat->seat_number
                    : null)
                ->filter()
                ->sort()
                ->values(),
            'total_seats' => $this->total_seats,
            'total_amount' => (float) $this->total_amount,
            'payment' => $this->successfulPayment ? [
                'payment_code' => $this->successfulPayment->payment_code,
                'method' => $this->successfulPayment->method,
                'provider' => $this->successfulPayment->provider,
                'paid_at' => $this->successfulPayment->paid_at?->toIso8601String(),
            ] : null,
        ];
    }
}
