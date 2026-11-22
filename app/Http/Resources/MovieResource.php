<?php

namespace App\Http\Resources;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class MovieResource
 *
 * SOLID - Single Responsibility Principle:
 * Bertanggung jawab khusus mentransformasikan entitas Movie menjadi format representasi JSON.
 *
 * @mixin Movie
 */
class MovieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'synopsis' => $this->synopsis,
            'genre' => $this->genre,
            'duration_minutes' => $this->duration_minutes,
            'duration_formatted' => $this->getDurationFormatted(),
            'format' => $this->format,
            'poster_url' => $this->poster_url,
            'release_date' => $this->release_date?->toDateString(),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
        ];
    }
}
