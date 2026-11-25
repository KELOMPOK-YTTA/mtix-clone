<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\ScheduleSeat;
use App\Models\Seat;
use App\Models\Studio;
use App\Models\User;
use App\Services\ScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Feature test modul Payment, Tiket & Admin (Anggota 6).
 * Jalankan: php artisan test --filter=PaymentAdminTest
 */
class PaymentAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private int $scheduleId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $cinema = Cinema::create(['name' => 'XXI Test', 'city' => 'Pontianak', 'address' => 'Jl. Uji']);
        $studio = Studio::create([
            'cinema_id' => $cinema->id,
            'name' => 'Studio 1',
            'type' => 'regular',
            'total_rows' => 1,
            'seats_per_row' => 3,
        ]);
        foreach ([1, 2, 3] as $number) {
            Seat::create(['studio_id' => $studio->id, 'row_label' => 'A', 'seat_number' => $number, 'is_gap' => false]);
        }

        $movie = Movie::factory()->nowPlaying()->create();

        $this->scheduleId = app(ScheduleService::class)->createSchedule([
            'movie_id' => $movie->id,
            'studio_id' => $studio->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '19:00',
        ])->id;
    }

    private function createBooking(int $seatCount = 2): int
    {
        $seatIds = ScheduleSeat::where('schedule_id', $this->scheduleId)->limit($seatCount)->pluck('id')->all();

        return $this->postJson('/api/bookings', ['schedule_id' => $this->scheduleId, 'seat_ids' => $seatIds])
            ->assertCreated()
            ->json('data.id');
    }

    public function test_user_can_pay_booking_and_get_ticket(): void
    {
        Sanctum::actingAs($this->user);
        $bookingId = $this->createBooking();

        $response = $this->postJson('/api/payments', [
            'booking_id' => $bookingId,
            'method' => 'ewallet',
            'provider' => 'gopay',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.payment.status', 'success')
            ->assertJsonPath('data.ticket.status', 'confirmed')
            ->assertJsonCount(2, 'data.ticket.seats');

        $booking = Booking::find($bookingId);
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame(2, ScheduleSeat::where('status', ScheduleSeat::STATUS_TAKEN)->count());

        $this->getJson('/api/tickets')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/tickets/{$booking->booking_code}")->assertOk()
            ->assertJsonPath('data.booking_code', $booking->booking_code);
    }

    public function test_booking_cannot_be_paid_twice(): void
    {
        Sanctum::actingAs($this->user);
        $bookingId = $this->createBooking();
        $payload = ['booking_id' => $bookingId, 'method' => 'bank_transfer', 'provider' => 'bca'];

        $this->postJson('/api/payments', $payload)->assertCreated();
        $this->postJson('/api/payments', $payload)
            ->assertStatus(422)
            ->assertJsonPath('message', 'Booking ini sudah dibayar.');
    }

    public function test_user_cannot_pay_other_users_booking(): void
    {
        Sanctum::actingAs($this->user);
        $bookingId = $this->createBooking();

        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/payments', ['booking_id' => $bookingId, 'method' => 'ewallet', 'provider' => 'ovo'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Booking tidak ditemukan.');
    }

    public function test_invalid_provider_is_rejected(): void
    {
        Sanctum::actingAs($this->user);
        $bookingId = $this->createBooking();

        $this->postJson('/api/payments', ['booking_id' => $bookingId, 'method' => 'ewallet', 'provider' => 'bca'])
            ->assertStatus(422);
    }

    public function test_regular_user_cannot_access_admin_routes(): void
    {
        Sanctum::actingAs($this->user);

        $this->getJson('/api/admin/dashboard')->assertForbidden()->assertJsonPath('status', 'error');
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $this->getJson('/api/admin/dashboard')->assertUnauthorized();
    }

    public function test_admin_dashboard_shows_revenue(): void
    {
        Sanctum::actingAs($this->user);
        $bookingId = $this->createBooking();
        $this->postJson('/api/payments', ['booking_id' => $bookingId, 'method' => 'ewallet', 'provider' => 'dana'])->assertCreated();
        $total = (float) Booking::find($bookingId)->total_amount;

        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
        $this->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('data.totals.tickets_sold', 2)
            ->assertJsonPath('data.bookings_by_status.confirmed', 1)
            ->assertJsonPath('data.revenue.total', $total);
    }

    public function test_admin_can_cancel_paid_booking_and_seats_are_released(): void
    {
        Sanctum::actingAs($this->user);
        $bookingId = $this->createBooking();
        $this->postJson('/api/payments', ['booking_id' => $bookingId, 'method' => 'ewallet', 'provider' => 'gopay'])->assertCreated();

        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
        $this->postJson("/api/admin/bookings/{$bookingId}/cancel")->assertOk();

        $this->assertSame('cancelled', Booking::find($bookingId)->status);
        $this->assertSame(0, ScheduleSeat::where('status', '!=', ScheduleSeat::STATUS_AVAILABLE)->count());
        $this->assertDatabaseHas('payments', ['booking_id' => $bookingId, 'status' => 'refunded']);
    }
}
