<?php

namespace Tests\Feature;

use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MovieTest extends TestCase
{
    use RefreshDatabase;

    // ─── Public Endpoints ────────────────────────────────────────────────

    public function test_public_can_get_all_movies(): void
    {
        Movie::factory()->count(3)->nowPlaying()->create();
        Movie::factory()->count(2)->comingSoon()->create();

        $response = $this->getJson('/api/movies');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data film berhasil diambil.',
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_public_can_filter_movies_by_now_playing_status(): void
    {
        Movie::factory()->count(3)->nowPlaying()->create();
        Movie::factory()->count(2)->comingSoon()->create();

        $response = $this->getJson('/api/movies?status=now_playing');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        foreach ($response->json('data') as $movie) {
            $this->assertEquals('now_playing', $movie['status']);
        }
    }

    public function test_public_can_filter_movies_by_coming_soon_status(): void
    {
        Movie::factory()->count(2)->nowPlaying()->create();
        Movie::factory()->count(3)->comingSoon()->create();

        $response = $this->getJson('/api/movies?status=coming_soon');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        foreach ($response->json('data') as $movie) {
            $this->assertEquals('coming_soon', $movie['status']);
        }
    }

    public function test_public_can_search_movies_by_keyword(): void
    {
        Movie::factory()->create([
            'title' => 'Petualangan Sang Naga',
            'slug' => 'petualangan-sang-naga',
            'genre' => 'Fantasy',
        ]);
        Movie::factory()->create([
            'title' => 'Cinta di Pantai',
            'slug' => 'cinta-di-pantai',
            'genre' => 'Romance',
        ]);

        $response = $this->getJson('/api/movies?search=Petualangan');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $this->assertEquals('Petualangan Sang Naga', $response->json('data.0.title'));
    }

    public function test_public_can_search_movies_by_genre(): void
    {
        Movie::factory()->create([
            'title' => 'Film Action Satu',
            'slug' => 'film-action-satu',
            'genre' => 'Action',
        ]);
        Movie::factory()->create([
            'title' => 'Film Lain',
            'slug' => 'film-lain',
            'genre' => 'Romance',
        ]);

        $response = $this->getJson('/api/movies?search=Action');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_public_can_get_movie_detail_by_slug(): void
    {
        $movie = Movie::factory()->create([
            'title' => 'Galaksi Terakhir',
            'slug' => 'galaksi-terakhir',
            'synopsis' => 'Sinopsis film galaksi.',
            'genre' => 'Sci-Fi',
            'duration_minutes' => 148,
            'format' => 'IMAX',
            'release_date' => '2026-12-05',
            'status' => 'coming_soon',
        ]);

        $response = $this->getJson('/api/movies/galaksi-terakhir');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Detail film berhasil diambil.',
                'data' => [
                    'slug' => 'galaksi-terakhir',
                    'title' => 'Galaksi Terakhir',
                    'genre' => 'Sci-Fi',
                    'format' => 'IMAX',
                    'status' => 'coming_soon',
                    'status_label' => 'Segera Tayang',
                    'duration_minutes' => 148,
                    'duration_formatted' => '2h 28m',
                ],
            ]);
    }

    public function test_get_movie_detail_returns_404_for_unknown_slug(): void
    {
        $response = $this->getJson('/api/movies/film-yang-tidak-ada');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Film tidak ditemukan.',
            ]);
    }

    public function test_movie_resource_does_not_expose_timestamps(): void
    {
        Movie::factory()->create(['slug' => 'test-movie']);

        $response = $this->getJson('/api/movies/test-movie');

        $data = $response->json('data');
        $this->assertArrayNotHasKey('created_at', $data);
        $this->assertArrayNotHasKey('updated_at', $data);
    }

    // ─── Admin CRUD Endpoints ────────────────────────────────────────────

    public function test_admin_can_create_movie_without_poster(): void
    {
        $payload = [
            'title' => 'Film Baru Tanpa Poster',
            'synopsis' => 'Sinopsis film baru yang menarik dan penuh aksi.',
            'genre' => 'Action',
            'duration_minutes' => 120,
            'format' => '3D',
            'release_date' => '2026-12-01',
            'status' => 'coming_soon',
        ];

        $response = $this->postJson('/api/admin/movies', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Film berhasil ditambahkan.',
                'data' => [
                    'title' => 'Film Baru Tanpa Poster',
                    'genre' => 'Action',
                    'format' => '3D',
                    'status' => 'coming_soon',
                ],
            ]);

        $this->assertDatabaseHas('movies', [
            'title' => 'Film Baru Tanpa Poster',
            'genre' => 'Action',
        ]);
    }

    public function test_admin_movie_slug_is_auto_generated_from_title(): void
    {
        $payload = [
            'title' => 'Judul Film Otomatis Slug',
            'synopsis' => 'Sinopsis singkat.',
            'genre' => 'Drama',
            'duration_minutes' => 90,
            'format' => '2D',
            'release_date' => '2026-12-10',
            'status' => 'coming_soon',
        ];

        $response = $this->postJson('/api/admin/movies', $payload);

        $response->assertStatus(201);
        $this->assertEquals('judul-film-otomatis-slug', $response->json('data.slug'));
        $this->assertDatabaseHas('movies', ['slug' => 'judul-film-otomatis-slug']);
    }

    public function test_admin_movie_slug_is_unique_when_title_is_duplicate(): void
    {
        Movie::factory()->create([
            'title' => 'Film Sama',
            'slug' => 'film-sama',
        ]);

        $payload = [
            'title' => 'Film Sama',
            'synopsis' => 'Sinopsis kedua.',
            'genre' => 'Comedy',
            'duration_minutes' => 100,
            'format' => '2D',
            'release_date' => '2026-12-15',
            'status' => 'coming_soon',
        ];

        $response = $this->postJson('/api/admin/movies', $payload);

        $response->assertStatus(201);
        $this->assertEquals('film-sama-1', $response->json('data.slug'));
    }

    public function test_admin_can_create_movie_with_poster_upload(): void
    {
        Storage::fake('public');

        $payload = [
            'title' => 'Film Dengan Poster',
            'synopsis' => 'Sinopsis film dengan poster.',
            'genre' => 'Horror',
            'duration_minutes' => 110,
            'format' => '2D',
            'release_date' => '2026-11-28',
            'status' => 'now_playing',
            'poster' => UploadedFile::fake()->image('poster.jpg', 400, 600),
        ];

        $response = $this->post('/api/admin/movies', $payload);

        $response->assertStatus(201);
        $this->assertNotNull($response->json('data.poster_url'));

        // Pastikan file poster tersimpan di dalam folder posters/
        $posterUrl = $response->json('data.poster_url');
        $this->assertStringContainsString('posters/', $posterUrl);
    }

    public function test_admin_create_movie_validates_required_fields(): void
    {
        $response = $this->postJson('/api/admin/movies', []);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validasi gagal, silakan periksa data film yang diinput.',
            ])
            ->assertJsonValidationErrors(['title', 'synopsis', 'genre', 'duration_minutes', 'format', 'release_date', 'status']);
    }

    public function test_admin_create_movie_validates_format_enum(): void
    {
        $payload = [
            'title' => 'Film Format Salah',
            'synopsis' => 'Sinopsis.',
            'genre' => 'Action',
            'duration_minutes' => 100,
            'format' => '4DX', // Format tidak valid
            'release_date' => '2026-12-01',
            'status' => 'now_playing',
        ];

        $response = $this->postJson('/api/admin/movies', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['format']);
    }

    public function test_admin_can_update_movie(): void
    {
        $movie = Movie::factory()->create([
            'title' => 'Judul Lama',
            'slug' => 'judul-lama',
            'status' => 'coming_soon',
        ]);

        $response = $this->putJson('/api/admin/movies/judul-lama', [
            'title' => 'Judul Baru',
            'status' => 'now_playing',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Film berhasil diperbarui.',
                'data' => [
                    'title' => 'Judul Baru',
                    'slug' => 'judul-baru',
                    'status' => 'now_playing',
                ],
            ]);

        $this->assertDatabaseHas('movies', [
            'id' => $movie->id,
            'title' => 'Judul Baru',
            'slug' => 'judul-baru',
            'status' => 'now_playing',
        ]);
    }

    public function test_admin_update_returns_404_for_unknown_movie(): void
    {
        $response = $this->putJson('/api/admin/movies/film-tidak-ada', [
            'title' => 'Judul Baru',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Film tidak ditemukan.',
            ]);
    }

    public function test_admin_can_delete_movie(): void
    {
        $movie = Movie::factory()->create(['slug' => 'film-dihapus']);

        $response = $this->deleteJson('/api/admin/movies/film-dihapus');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Film berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('movies', ['slug' => 'film-dihapus']);
    }

    public function test_admin_delete_returns_404_for_unknown_movie(): void
    {
        $response = $this->deleteJson('/api/admin/movies/film-tidak-ada');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Film tidak ditemukan.',
            ]);
    }
}
