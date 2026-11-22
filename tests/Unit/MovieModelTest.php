<?php

namespace Tests\Unit;

use App\Contracts\MovieInterface;
use App\Models\Abstract\BaseContent;
use App\Models\Movie;
use App\Repositories\MovieRepository;
use ReflectionClass;
use Tests\TestCase;

class MovieModelTest extends TestCase
{
    /**
     * Uji konsep PBO: ABSTRAKSI (BaseContent adalah abstract class dan tidak bisa di-instantiate langsung).
     */
    public function test_base_content_is_an_abstract_class(): void
    {
        $reflection = new ReflectionClass(BaseContent::class);
        $this->assertTrue($reflection->isAbstract(), 'BaseContent harus berupa class abstrak.');
        $this->assertTrue($reflection->hasMethod('getStatusLabel'), 'BaseContent harus memiliki method getStatusLabel.');
        $this->assertTrue($reflection->getMethod('getStatusLabel')->isAbstract(), 'Method getStatusLabel harus bersifat abstrak.');
    }

    /**
     * Uji konsep PBO: INHERITANCE (Movie mewarisi BaseContent).
     */
    public function test_movie_inherits_from_base_content(): void
    {
        $movie = new Movie;
        $this->assertInstanceOf(BaseContent::class, $movie, 'Movie harus merupakan turunan dari BaseContent.');
    }

    /**
     * Uji konsep PBO: POLIMORFISME (getStatusLabel menghasilkan label sesuai nilai status).
     */
    public function test_polymorphic_status_label(): void
    {
        $nowPlayingMovie = new Movie(['status' => 'now_playing']);
        $comingSoonMovie = new Movie(['status' => 'coming_soon']);
        $unknownMovie = new Movie(['status' => 'archived']);

        $this->assertEquals('Sedang Tayang', $nowPlayingMovie->getStatusLabel());
        $this->assertEquals('Segera Tayang', $comingSoonMovie->getStatusLabel());
        $this->assertEquals('Tidak Diketahui', $unknownMovie->getStatusLabel());
    }

    /**
     * Uji konsep PBO: ENKAPSULASI (timestamps disembunyikan dari serialisasi JSON / array).
     */
    public function test_encapsulation_hides_timestamps(): void
    {
        $movie = new Movie([
            'title' => 'Film Tes',
            'slug' => 'film-tes',
            'synopsis' => 'Sinopsis.',
            'genre' => 'Action',
            'duration_minutes' => 120,
            'format' => '2D',
            'release_date' => '2026-12-01',
            'status' => 'coming_soon',
        ]);

        $array = $movie->toArray();

        $this->assertArrayNotHasKey('created_at', $array, 'created_at harus disembunyikan.');
        $this->assertArrayNotHasKey('updated_at', $array, 'updated_at harus disembunyikan.');
    }

    /**
     * Uji ENKAPSULASI: Fillable attributes terdefinisi dengan benar.
     */
    public function test_encapsulation_fillable_attributes(): void
    {
        $movie = new Movie;
        $fillable = $movie->getFillable();

        $this->assertContains('slug', $fillable);
        $this->assertContains('title', $fillable);
        $this->assertContains('synopsis', $fillable);
        $this->assertContains('genre', $fillable);
        $this->assertContains('duration_minutes', $fillable);
        $this->assertContains('format', $fillable);
        $this->assertContains('poster_url', $fillable);
        $this->assertContains('release_date', $fillable);
        $this->assertContains('status', $fillable);
    }

    /**
     * Uji method getDurationFormatted() pada BaseContent.
     * Format output: "{h}h {m}m"
     */
    public function test_get_duration_formatted_returns_hours_and_minutes(): void
    {
        $movie = new Movie(['duration_minutes' => 135]);
        $this->assertEquals('2h 15m', $movie->getDurationFormatted());
    }

    /**
     * Uji getDurationFormatted() untuk durasi yang tepat 1 jam (tanpa menit).
     */
    public function test_get_duration_formatted_returns_only_hours_when_no_remaining_minutes(): void
    {
        $movie = new Movie(['duration_minutes' => 120]);
        $this->assertEquals('2h', $movie->getDurationFormatted());
    }

    /**
     * Uji getDurationFormatted() untuk durasi di bawah 1 jam.
     */
    public function test_get_duration_formatted_returns_only_minutes_when_less_than_one_hour(): void
    {
        $movie = new Movie(['duration_minutes' => 45]);
        $this->assertEquals('45m', $movie->getDurationFormatted());
    }

    /**
     * Uji getDurationFormatted() untuk durasi tepat 1 jam lebih beberapa menit.
     */
    public function test_get_duration_formatted_handles_148_minutes(): void
    {
        $movie = new Movie(['duration_minutes' => 148]);
        $this->assertEquals('2h 28m', $movie->getDurationFormatted());
    }

    /**
     * Uji bahwa Movie mengimplementasikan interface MovieInterface melalui MovieRepository.
     */
    public function test_movie_interface_binding_is_registered(): void
    {
        $repository = app(MovieInterface::class);
        $this->assertInstanceOf(MovieRepository::class, $repository);
    }
}
