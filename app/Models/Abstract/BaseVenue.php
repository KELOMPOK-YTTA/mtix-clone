<?php

namespace App\Models\Abstract;

use Illuminate\Database\Eloquent\Model;

abstract class BaseVenue extends Model
{
    protected $hidden = ['created_at', 'updated_at'];

    abstract public function getVenueLabel(): string;
}