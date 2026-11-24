<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ScheduleResource
 *
 * Mengatur format JSON untuk data jadwal tayang.
 *
 * @mixin \App\Models\Schedule
 */
class ScheduleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'time' => $this->getTimeLabel(),
            'end_time' => $this->whenLoaded('movie', fn () => $this->getEndsAt()->format('H:i')),
            'price' => $this->price,
            'format' => $this->format,
            'available_seats' => $this->whenCounted('availableSeats'),
            'movie' => $this->whenLoaded('movie', fn () => [
                'id' => $this->movie->id,
                'slug' => $this->movie->slug,
                'title' => $this->movie->title,
                'duration' => $this->movie->getDurationFormatted(),
                'poster_url' => $this->movie->poster_url,
            ]),
            'studio' => $this->whenLoaded('studio', fn () => [
                'id' => $this->studio->id,
                'name' => $this->studio->name,
                'type' => $this->studio->type,
                'label' => $this->studio->getVenueLabel(),
                'cinema' => $this->studio->relationLoaded('cinema') ? [
                    'id' => $this->studio->cinema->id,
                    'name' => $this->studio->cinema->name,
                    'city' => $this->studio->cinema->city,
                    'label' => $this->studio->cinema->getVenueLabel(),
                ] : null,
            ]),
        ];
    }
}
