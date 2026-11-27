<?php

namespace App\Models;

use App\Models\Abstract\BaseTransaction;
use App\Traits\HasBookingCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Payment
 *
 * INHERITANCE (OOP): Payment mewarisi BaseTransaction (sama seperti Booking),
 * sehingga otomatis punya relasi user() dan method isPending().
 */
class Payment extends BaseTransaction
{
    use HasBookingCode;

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REFUNDED = 'refunded';

    /**
     * ENKAPSULASI (OOP): hanya kolom ini yang boleh diisi mass-assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'payment_code',
        'booking_id',
        'user_id',
        'method',
        'provider',
        'reference',
        'amount',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public static function generateCode(): string
    {
        return static::generateUniqueCode('PAY-', 'payment_code', 8);
    }

    /**
     * POLIMORFISME (OOP): implementasi method abstrak dari BaseTransaction.
     * Untuk Payment, total = jumlah yang dibayarkan.
     */
    public function calculateTotal(): float
    {
        return (float) $this->amount;
    }

    /**
     * POLIMORFISME (OOP): Payment dianggap kedaluwarsa jika masih pending
     * tetapi booking-nya sudah melewati batas waktu bayar.
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_PENDING && (bool) $this->booking?->expires_at?->isPast();
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }
}
