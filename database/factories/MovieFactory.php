<?php

namespace Database\Factories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Movie>
 */
class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'slug' => Str::slug($title),
            'title' => ucwords($title),
            'synopsis' => fake()->paragraph(3),
            'genre' => fake()->randomElement(['Action', 'Drama', 'Comedy', 'Horror', 'Thriller', 'Animation', 'Romance', 'Sci-Fi']),
            'duration_minutes' => fake()->numberBetween(80, 180),
            'format' => fake()->randomElement(['2D', '3D', 'IMAX']),
            'poster_url' => null,
            'release_date' => fake()->dateTimeBetween('-3 months', '+3 months')->format('Y-m-d'),
            'status' => fake()->randomElement(['now_playing', 'coming_soon']),
        ];
    }

    /**
     * State untuk film yang sedang tayang.
     */
    public function nowPlaying(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'now_playing',
            'release_date' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
        ]);
    }

    /**
     * State untuk film yang akan segera tayang.
     */
    public function comingSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'coming_soon',
            'release_date' => fake()->dateTimeBetween('+1 days', '+3 months')->format('Y-m-d'),
        ]);
    }
}
