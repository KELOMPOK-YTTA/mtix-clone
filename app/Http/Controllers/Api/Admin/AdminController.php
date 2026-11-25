<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\Studio;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Class AdminController (Anggota 6)
 *
 * GET /api/admin/dashboard — ringkasan statistik untuk halaman dashboard admin.
 */
class AdminController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Statistik dashboard berhasil diambil.',
            'data' => [
                'totals' => $this->totals(),
                'revenue' => $this->revenue(),
                'bookings_by_status' => $this->bookingsByStatus(),
                'top_movies' => $this->topMovies(),
                'recent_payments' => $this->recentPayments(),
            ],
        ]);
    }

    private function totals(): array
    {
        return [
            'users' => User::where('role', 'user')->count(),
            'movies' => Movie::count(),
            'movies_now_playing' => Movie::where('status', 'now_playing')->count(),
            'cinemas' => Cinema::count(),
            'studios' => Studio::count(),
            'upcoming_schedules' => Schedule::upcoming()->count(),
            'bookings' => Booking::count(),
            'tickets_sold' => (int) Booking::where('status', 'confirmed')->sum('total_seats'),
        ];
    }

    private function revenue(): array
    {
        $lastSevenDays = Payment::successful()
            ->where('paid_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(paid_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        // Isi hari tanpa transaksi dengan 0 supaya grafik di frontend tetap 7 titik
        $chart = collect(range(6, 0))->map(function (int $daysAgo) use ($lastSevenDays) {
            $day = now()->subDays($daysAgo)->toDateString();

            return ['date' => $day, 'total' => (float) ($lastSevenDays[$day] ?? 0)];
        });

        return [
            'total' => (float) Payment::successful()->sum('amount'),
            'today' => (float) Payment::successful()->whereDate('paid_at', now()->toDateString())->sum('amount'),
            'last_7_days' => $chart,
        ];
    }

    private function bookingsByStatus(): array
    {
        $counts = Booking::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(['pending', 'confirmed', 'cancelled', 'expired'])
            ->mapWithKeys(fn (string $status) => [$status => (int) ($counts[$status] ?? 0)])
            ->all();
    }

    private function topMovies(): array
    {
        return Booking::query()
            ->join('schedules', 'schedules.id', '=', 'bookings.schedule_id')
            ->join('movies', 'movies.id', '=', 'schedules.movie_id')
            ->where('bookings.status', 'confirmed')
            ->groupBy('movies.id', 'movies.title')
            ->select(
                'movies.id',
                'movies.title',
                DB::raw('SUM(bookings.total_seats) as tickets_sold'),
                DB::raw('SUM(bookings.total_amount) as revenue'),
            )
            ->orderByDesc('tickets_sold')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'title' => $row->title,
                'tickets_sold' => (int) $row->tickets_sold,
                'revenue' => (float) $row->revenue,
            ])
            ->all();
    }

    private function recentPayments(): array
    {
        return Payment::with(['user', 'booking.schedule.movie'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Payment $payment) => [
                'payment_code' => $payment->payment_code,
                'booking_code' => $payment->booking?->booking_code,
                'user' => $payment->user?->name,
                'movie' => $payment->booking?->schedule?->movie?->title,
                'method' => $payment->method,
                'provider' => $payment->provider,
                'amount' => (float) $payment->amount,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at?->toIso8601String(),
            ])
            ->all();
    }
}
