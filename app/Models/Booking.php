<?php

namespace App\Models;

use App\Models\Abstract\BaseTransaction;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Booking extends BaseTransaction
{
    protected $fillable = [
        'booking_code',
        'user_id',
        'schedule_id',
        'total_seats',
        'subtotal',
        'admin_fee',
        'total_amount',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'expires_at' => 'datetime',
        ];
    }

    public static function generateCode(): string
    {
        return 'MTX-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
    }

    public function calculateTotal(): float
    {
        return (float) ($this->subtotal + $this->admin_fee);
    }

    public function isExpired(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isPast();
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function bookingSeats(): HasMany
    {
        return $this->hasMany(BookingSeat::class);
    }
}
