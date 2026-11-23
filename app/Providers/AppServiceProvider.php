<?php

namespace App\Providers;

use App\Contracts\AuthServiceInterface;
use App\Contracts\MovieInterface;
use App\Contracts\ScheduleRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Repositories\MovieRepository;
use App\Repositories\ScheduleRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // SOLID - Dependency Inversion Principle:
        // Binding Interface/Abstraksi ke Implementasi Konkret
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);

        // Modul Film (Anggota 2)
        $this->app->bind(MovieInterface::class, MovieRepository::class);

        // Modul Jadwal Tayang (Anggota 4)
        $this->app->bind(ScheduleRepositoryInterface::class, ScheduleRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
