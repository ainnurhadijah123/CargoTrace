<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // <-- Wajib dipanggil untuk membuka gerbang

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Trik Sapu Jagat: Selalu loloskan semua user dari segala pengecekan Gate/Policy
        Gate::before(function ($user, $ability) {
            return true;
        });
    }
}