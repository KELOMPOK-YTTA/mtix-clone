<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = [
        'studio_id',
        'row_label',
        'seat_number',
        'is_gap',
    ];

    protected $casts = [
        'is_gap' => 'boolean',
    ];

    public function studio()
    {
        return $this->belongsTo(Studio::class);
    }
}