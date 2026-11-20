<?php

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
