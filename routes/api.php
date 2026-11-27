<?php

use App\Http\Controllers\Api\BookingController;

use App\Http\Controllers\Api\Admin\AdminBookingController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\AdminMovieController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CinemaController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\StudioController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// â”€â”€ Modul Cinema, Studio & Seat (Anggota 3) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
Route::get('cinemas', [CinemaController::class, 'index']);
Route::get('cinemas/{id}', [CinemaController::class, 'show']);
Route::get('cinemas/{id}/studios', [StudioController::class, 'indexByCinema']);
Route::get('studios/{id}/seats', [StudioController::class, 'seats']);

// Tulis data bioskop/studio hanya untuk admin (middleware dari Anggota 6)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('cinemas', [CinemaController::class, 'store']);
    Route::put('cinemas/{id}', [CinemaController::class, 'update']);
    Route::delete('cinemas/{id}', [CinemaController::class, 'destroy']);
    Route::post('cinemas/{id}/studios', [StudioController::class, 'store']);
    Route::put('studios/{id}', [StudioController::class, 'update']);
    Route::delete('studios/{id}', [StudioController::class, 'destroy']);
});

// â”€â”€ Modul Auth & User Management (Anggota 1) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// Public Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected Routes (Auth Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth Session
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // User Profile
    Route::get('user', [UserController::class, 'show']);
    Route::match(['put', 'patch'], 'user', [UserController::class, 'update']);

    // Modul Booking & Seat Selection (Anggota 5)
    Route::get('bookings', [BookingController::class, 'index']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings/{id}', [BookingController::class, 'show'])->whereNumber('id');
    Route::post('bookings/{id}/cancel', [BookingController::class, 'cancel'])->whereNumber('id');

    // Modul Payment & Tiket (Anggota 6)
    Route::get('payments/methods', [PaymentController::class, 'methods']);
    Route::get('payments', [PaymentController::class, 'index']);
    Route::post('payments', [PaymentController::class, 'store']);
    Route::get('tickets', [TicketController::class, 'index']);
    Route::get('tickets/{code}', [TicketController::class, 'show']);
});

// â”€â”€ Modul Film (Anggota 2) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// Public: Semua pengunjung dapat melihat daftar dan detail film
Route::get('movies', [MovieController::class, 'index']);
Route::get('movies/{slug}', [MovieController::class, 'show']);

// â”€â”€ Modul Jadwal Tayang (Anggota 4) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// Public: daftar jadwal (filter movie_id, cinema_id, studio_id, city, date, time_from),
// detail jadwal, dan layout kursi + status per jadwal (dipakai modul Booking).
Route::get('schedules', [ScheduleController::class, 'index']);
Route::get('schedules/{id}', [ScheduleController::class, 'show'])->whereNumber('id');
Route::get('schedules/{id}/seats', [ScheduleController::class, 'seats'])->whereNumber('id');

// == Admin Panel (Anggota 6) =========================================
// Semua route /api/admin/* wajib login (Sanctum) DAN role admin
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('dashboard', [AdminController::class, 'index']);

    Route::get('bookings', [AdminBookingController::class, 'index']);
    Route::get('bookings/{id}', [AdminBookingController::class, 'show'])->whereNumber('id');
    Route::post('bookings/{id}/cancel', [AdminBookingController::class, 'cancel'])->whereNumber('id');

    Route::get('users', [AdminUserController::class, 'index']);
    Route::patch('users/{id}/role', [AdminUserController::class, 'updateRole'])->whereNumber('id');

    // Kelola film (Anggota 2) & jadwal (Anggota 4)
    Route::post('movies', [AdminMovieController::class, 'store']);
    Route::put('movies/{slug}', [AdminMovieController::class, 'update']);
    Route::delete('movies/{slug}', [AdminMovieController::class, 'destroy']);

    Route::post('schedules', [ScheduleController::class, 'store']);
    Route::put('schedules/{id}', [ScheduleController::class, 'update'])->whereNumber('id');
    Route::delete('schedules/{id}', [ScheduleController::class, 'destroy'])->whereNumber('id');
});

