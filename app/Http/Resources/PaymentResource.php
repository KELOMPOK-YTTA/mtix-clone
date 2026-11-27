<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Format JSON data pembayaran.
 *
 * @mixin \App\Models\Payment
 */
class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_code' => $this->payment_code,
            'method' => $this->method,
            'provider' => $this->provider,
            'reference' => $this->reference,
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'booking' => $this->whenLoaded('booking', fn () => [
                'id' => $this->booking->id,
                'booking_code' => $this->booking->booking_code,
                'status' => $this->booking->status,
                'movie_title' => $this->booking->relationLoaded('schedule')
                    ? $this->booking->schedule?->movie?->title
                    : null,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
