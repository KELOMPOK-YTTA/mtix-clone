<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ScheduleSeat
 *
 * Status satu kursi pada satu jadwal tertentu.
 * Modul Booking (Anggota 5) mengubah status kursi lewat model ini.
 */
class ScheduleSeat extends Model
{
    public const STATUS_AVAILABLE = 'available';

    /** Sudah dipesan, menunggu pembayaran. */
    public const STATUS_BOOKED = 'booked';

    /** Sudah dibayar. */
    public const STATUS_TAKEN = 'taken';

    /**
     * @var list<string>
     */
    protected $fillable = ['schedule_id', 'seat_id', 'status'];

    /**
     * @var list<string>
     */
    protected $hidden = ['created_at', 'updated_at'];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }
}
