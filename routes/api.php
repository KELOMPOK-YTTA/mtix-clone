<?php

use App\Http\Controllers\Api\BookingController;

use App\Http\Controllers\Api\Admin\AdminMovieController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CinemaController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\StudioController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// â”€â”€ Modul Cinema, Studio & Seat (Anggota 3) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
Route::get('cinemas', [CinemaController::class, 'index']);
Route::get('cinemas/{id}', [CinemaController::class, 'show']);
Route::get('cinemas/{id}/studios', [StudioController::class, 'indexByCinema']);
Route::get('studios/{id}/seats', [StudioController::class, 'seats']);

Route::post('cinemas', [CinemaController::class, 'store']);
Route::put('cinemas/{id}', [CinemaController::class, 'update']);
Route::delete('cinemas/{id}', [CinemaController::class, 'destroy']);
Route::post('cinemas/{id}/studios', [StudioController::class, 'store']);
Route::put('studios/{id}', [StudioController::class, 'update']);
Route::delete('studios/{id}', [StudioController::class, 'destroy']);

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

// Admin: Kelola film & jadwal (CRUD) â€” gunakan prefix "admin"
// TODO: Pasang middleware auth:sanctum + admin setelah Anggota 6 selesai mengimplementasikan
//       middleware/policy admin. Contoh: Route::middleware(['auth:sanctum', 'admin'])->group(...)
Route::prefix('admin')->group(function () {
    Route::post('movies', [AdminMovieController::class, 'store']);
    Route::put('movies/{slug}', [AdminMovieController::class, 'update']);
    Route::delete('movies/{slug}', [AdminMovieController::class, 'destroy']);

    Route::post('schedules', [ScheduleController::class, 'store']);
    Route::put('schedules/{id}', [ScheduleController::class, 'update'])->whereNumber('id');
    Route::delete('schedules/{id}', [ScheduleController::class, 'destroy'])->whereNumber('id');
});

