<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Schedule;
use App\Models\ScheduleSeat;
use App\Models\Seat;
use App\Models\Studio;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test modul Jadwal Tayang (Anggota 4).
 * Jalankan: php artisan test --filter=ScheduleApiTest
 */
class ScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    private Movie $movie;

    private Studio $studio;

    private string $tomorrow;

    protected function setUp(): void
    {
        parent::setUp();

        // Route /api/admin/* sekarang dilindungi middleware admin (Anggota 6)
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));

        $this->tomorrow = now()->addDay()->toDateString();

        $cinema = Cinema::create([
            'name' => 'XXI Test',
            'city' => 'Pontianak',
            'address' => 'Jl. Uji Coba No. 1',
        ]);

        // Studio regular → Studio::getPrice() = 45000
        $this->studio = Studio::create([
            'cinema_id' => $cinema->id,
            'name' => 'Studio 1',
            'type' => 'regular',
            'total_rows' => 2,
            'seats_per_row' => 3,
        ]);

        // 6 posisi kursi, 1 di antaranya lorong → 5 kursi fisik
        foreach (['A', 'B'] as $rowLabel) {
            foreach ([1, 2, 3] as $seatNumber) {
                Seat::create([
                    'studio_id' => $this->studio->id,
                    'row_label' => $rowLabel,
                    'seat_number' => $seatNumber,
                    'is_gap' => $rowLabel === 'B' && $seatNumber === 2,
                ]);
            }
        }

        // Durasi 120 menit → tayang 19:00 selesai 21:00, studio bisa dipakai lagi 21:15
        $this->movie = Movie::create([
            'slug' => 'film-uji',
            'title' => 'Film Uji',
            'synopsis' => 'Sinopsis film uji.',
            'genre' => 'Drama',
            'duration_minutes' => 120,
            'format' => '2D',
            'release_date' => now()->subWeek()->toDateString(),
            'status' => 'now_playing',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function schedulePayload(array $overrides = []): array
    {
        return array_merge([
            'movie_id' => $this->movie->id,
            'studio_id' => $this->studio->id,
            'date' => $this->tomorrow,
            'time' => '19:00',
        ], $overrides);
    }

    public function test_admin_can_create_schedule_and_seats_are_copied_from_studio(): void
    {
        $response = $this->postJson('/api/admin/schedules', $this->schedulePayload());

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.time', '19:00')
            ->assertJsonPath('data.end_time', '21:00')
            ->assertJsonPath('data.price', 45000)
            ->assertJsonPath('data.format', '2D')
            ->assertJsonPath('data.available_seats', 5);

        $this->assertDatabaseCount('schedule_seats', 5);
    }

    public function test_overlapping_schedule_in_same_studio_is_rejected(): void
    {
        $this->postJson('/api/admin/schedules', $this->schedulePayload())->assertCreated();

        $this->postJson('/api/admin/schedules', $this->schedulePayload(['time' => '20:00']))
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['errors' => ['time']]);

        $this->assertDatabaseCount('schedules', 1);
    }

    public function test_schedule_after_cleaning_break_is_allowed(): void
    {
        $this->postJson('/api/admin/schedules', $this->schedulePayload())->assertCreated();

        $this->postJson('/api/admin/schedules', $this->schedulePayload(['time' => '21:15']))
            ->assertCreated();
    }

    public function test_invalid_input_returns_standard_error_format(): void
    {
        $this->postJson('/api/admin/schedules', $this->schedulePayload(['time' => '7.30']))
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['errors' => ['time']]);
    }

    public function test_schedules_can_be_filtered_by_movie_and_date(): void
    {
        $this->postJson('/api/admin/schedules', $this->schedulePayload())->assertCreated();
        $this->postJson('/api/admin/schedules', $this->schedulePayload([
            'date' => now()->addDays(2)->toDateString(),
        ]))->assertCreated();

        $this->getJson("/api/schedules?movie_id={$this->movie->id}&date={$this->tomorrow}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.movie.title', 'Film Uji')
            ->assertJsonPath('data.0.studio.cinema.city', 'Pontianak');
    }

    public function test_seat_map_is_grouped_by_row(): void
    {
        $scheduleId = $this->postJson('/api/admin/schedules', $this->schedulePayload())->json('data.id');

        $this->getJson("/api/schedules/{$scheduleId}/seats")
            ->assertOk()
            ->assertJsonCount(2, 'data.rows')
            ->assertJsonPath('data.rows.0.row', 'A')
            ->assertJsonCount(3, 'data.rows.0.seats')
            ->assertJsonCount(2, 'data.rows.1.seats')
            ->assertJsonPath('data.rows.0.seats.0.label', 'A1')
            ->assertJsonPath('data.rows.0.seats.0.status', 'available');
    }

    public function test_schedule_with_booked_seat_cannot_be_deleted(): void
    {
        $scheduleId = $this->postJson('/api/admin/schedules', $this->schedulePayload())->json('data.id');

        ScheduleSeat::where('schedule_id', $scheduleId)->first()
            ->update(['status' => ScheduleSeat::STATUS_BOOKED]);

        $this->deleteJson("/api/admin/schedules/{$scheduleId}")
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error');

        $this->assertDatabaseHas('schedules', ['id' => $scheduleId]);
    }

    public function test_schedule_without_bookings_can_be_deleted(): void
    {
        $scheduleId = $this->postJson('/api/admin/schedules', $this->schedulePayload())->json('data.id');

        $this->deleteJson("/api/admin/schedules/{$scheduleId}")->assertOk();

        $this->assertDatabaseMissing('schedules', ['id' => $scheduleId]);
        $this->assertDatabaseCount('schedule_seats', 0);
    }

    public function test_price_can_be_updated_but_not_set_to_null(): void
    {
        $scheduleId = $this->postJson('/api/admin/schedules', $this->schedulePayload())->json('data.id');

        $this->putJson("/api/admin/schedules/{$scheduleId}", ['price' => 50000])
            ->assertOk()
            ->assertJsonPath('data.price', 50000);

        $this->putJson("/api/admin/schedules/{$scheduleId}", ['price' => null])
            ->assertUnprocessable();

        $this->assertSame(50000, Schedule::find($scheduleId)->price);
    }

    public function test_unknown_schedule_returns_404(): void
    {
        $this->getJson('/api/schedules/999')
            ->assertNotFound()
            ->assertJsonPath('status', 'error');
    }
}
