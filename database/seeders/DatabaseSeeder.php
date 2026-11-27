<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default Admin User untuk testing dan operasional sistem
        User::firstOrCreate(
            ['phone' => '081200000001'],
            [
                'name' => 'Administrator Cinema XXI',
                'pin' => '123456',
                'role' => 'admin',
            ]
        );

        // Default Member User untuk simulasi pemesanan
        User::firstOrCreate(
            ['phone' => '081200000002'],
            [
                'name' => 'Member Cinema XXI',
                'pin' => '123456',
                'role' => 'user',
            ]
        );

        $this->call([
            CinemaSeeder::class,
            MovieSeeder::class,
            ScheduleSeeder::class,
        ]);
    }
}
