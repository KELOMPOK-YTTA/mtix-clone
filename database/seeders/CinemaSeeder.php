<?php

namespace Database\Seeders;

use App\Models\Cinema;
use App\Services\SeatLayoutService;
use Illuminate\Database\Seeder;

class CinemaSeeder extends Seeder
{
    public function run(): void
    {
        $seatService = new SeatLayoutService();

        $cinemasData = [
            [
                'name'    => 'Ayani Megamall XXI',
                'city'    => 'Pontianak',
                'address' => 'Jl. Jenderal Ahmad Yani No. 4, Pontianak Selatan',
                'studios' => [
                    ['name' => 'Studio 1', 'type' => 'regular',  'total_rows' => 8, 'seats_per_row' => 12, 'gaps' => [4, 9]],
                    ['name' => 'The Premiere 1', 'type' => 'premiere', 'total_rows' => 5, 'seats_per_row' => 8, 'gaps' => [5]],
                ],
            ],
            [
                'name'    => 'Grand Indonesia XXI',
                'city'    => 'Jakarta',
                'address' => 'Jl. M.H. Thamrin No. 1, Jakarta Pusat',
                'studios' => [
                    ['name' => 'Studio 1', 'type' => 'imax',    'total_rows' => 10, 'seats_per_row' => 14, 'gaps' => [5, 10]],
                    ['name' => 'Studio 2', 'type' => 'regular', 'total_rows' => 8,  'seats_per_row' => 12, 'gaps' => [6, 7]],
                ],
            ],
            [
                'name'    => 'Ciwalk XXI',
                'city'    => 'Bandung',
                'address' => 'Jl. Cihampelas No. 160, Bandung',
                'studios' => [
                    ['name' => 'Studio 1', 'type' => 'regular',  'total_rows' => 8, 'seats_per_row' => 12, 'gaps' => [4, 9]],
                    ['name' => 'The Premiere 1', 'type' => 'premiere', 'total_rows' => 4, 'seats_per_row' => 6, 'gaps' => [3, 4]],
                ],
            ],
        ];

        foreach ($cinemasData as $data) {
            $cinema = Cinema::create([
                'name'    => $data['name'],
                'city'    => $data['city'],
                'address' => $data['address'],
            ]);

            foreach ($data['studios'] as $studioData) {
                $studio = $cinema->studios()->create([
                    'name'          => $studioData['name'],
                    'type'          => $studioData['type'],
                    'total_rows'    => $studioData['total_rows'],
                    'seats_per_row' => $studioData['seats_per_row'],
                ]);

                $seatService->generateSeats($studio, $studioData['gaps']);
            }
        }
    }
}