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