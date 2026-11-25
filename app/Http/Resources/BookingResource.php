<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_code' => $this->booking_code,
            'status' => $this->status,
            'total_seats' => $this->total_seats,
            'subtotal' => (float) $this->subtotal,
            'admin_fee' => (float) $this->admin_fee,
            'total_amount' => (float) $this->total_amount,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'schedule' => $this->whenLoaded('schedule', function () {
                return [
                    'id' => $this->schedule->id,
                    'show_date' => $this->schedule->show_date,
                    'start_time' => $this->schedule->start_time,
                    'end_time' => $this->schedule->end_time,
                    'price' => (float) $this->schedule->price,
                    'movie' => $this->schedule->relationLoaded('movie') ? [
                        'id' => $this->schedule->movie->id,
                        'title' => $this->schedule->movie->title,
                        'poster_url' => $this->schedule->movie->poster_url ?? null,
                    ] : null,
                    'studio' => $this->schedule->relationLoaded('studio') ? [
                        'id' => $this->schedule->studio->id,
                        'name' => $this->schedule->studio->name,
                        'type' => $this->schedule->studio->type,
                        'cinema' => $this->schedule->studio->relationLoaded('cinema') ? [
                            'id' => $this->schedule->studio->cinema->id,
                            'name' => $this->schedule->studio->cinema->name,
                            'city' => $this->schedule->studio->cinema->city,
                        ] : null,
                    ] : null,
                ];
            }),
            'seats' => $this->whenLoaded('bookingSeats', function () {
                return $this->bookingSeats->map(function ($bookingSeat) {
                    $seat = $bookingSeat->scheduleSeat?->seat;
                    return [
                        'schedule_seat_id' => $bookingSeat->schedule_seat_id,
                        'seat_number' => $seat?->seat_number,
                        'row_label' => $seat?->row_label,
                        'column_number' => $seat?->column_number,
                        'price' => (float) $bookingSeat->price,
                    ];
                });
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
