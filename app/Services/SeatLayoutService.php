<?php

namespace App\Services;

use App\Models\Seat;
use App\Models\Studio;

class SeatLayoutService
{
    public function generateSeats(Studio $studio, array $gapNumbers = []): void
    {
        $studio->seats()->delete();

        $seats = [];
        $now = now();

        for ($r = 0; $r < $studio->total_rows; $r++) {
            $rowLabel = chr(65 + $r);

            for ($num = 1; $num <= $studio->seats_per_row; $num++) {
                $seats[] = [
                    'studio_id'   => $studio->id,
                    'row_label'   => $rowLabel,
                    'seat_number' => $num,
                    'is_gap'      => in_array($num, $gapNumbers),
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
        }

        Seat::insert($seats);
    }
}