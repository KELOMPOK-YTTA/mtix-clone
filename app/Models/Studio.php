<?php

namespace App\Models;

use App\Models\Abstract\BaseVenue;

class Studio extends BaseVenue
{
    protected $fillable = [
        'cinema_id',
        'name',
        'type',
        'total_rows',
        'seats_per_row',
    ];

    public function getVenueLabel(): string
    {
        return "{$this->name} - " . strtoupper($this->type);
    }

    public function getPrice(): int
    {
        return match ($this->type) {
            'premiere' => 100000,
            'imax'     => 75000,
            default    => 45000,
        };
    }

    public function cinema()
    {
        return $this->belongsTo(Cinema::class);
    }

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }
}