<?php

namespace App\Models;

use App\Models\Abstract\BaseTransaction;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\HasBookingCode;

class Booking extends BaseTransaction
{
    use HasBookingCode;

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
        // Kode dijamin unik lewat trait HasBookingCode (Anggota 6)
        return static::generateUniqueCode('MTX-'.now()->format('Ymd').'-', 'booking_code', 4);
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** Pembayaran yang berhasil untuk booking ini (Anggota 6). */
    public function successfulPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->ofMany(
            ['id' => 'max'],
            fn ($query) => $query->where('status', Payment::STATUS_SUCCESS),
        );
    }
}
