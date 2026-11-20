<?php

use App\Http\Controllers\Api\CinemaController;
use App\Http\Controllers\Api\StudioController;
use Illuminate\Support\Facades\Route;

// Modul Cinema, Studio & Seat (Anggota 3)
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
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Cinema XXI Clone (M-Tix)
|--------------------------------------------------------------------------
| Modul Anggota 1: Auth & User Management
| Branch: feature/auth
*/

// ── Public Auth Routes ───────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// ── Protected Routes (Auth Sanctum) ──────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    // Auth Session
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // User Profile
    Route::get('user', [UserController::class, 'show']);
    Route::match(['put', 'patch'], 'user', [UserController::class, 'update']);
});
