<?php

namespace App\Models;

use App\Models\Abstract\BaseVenue;

class Cinema extends BaseVenue
{
    protected $fillable = ['name', 'city', 'address'];

    public function getVenueLabel(): string
    {
        return "{$this->name} ({$this->city})";
    }

    public function studios()
    {
        return $this->hasMany(Studio::class);
    }
}