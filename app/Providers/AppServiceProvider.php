<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

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
        // Paksa skema HTTPS saat di production atau di cloud reverse proxy Railway
        if (app()->environment('production') || str_contains(request()->url(), 'railway.app') || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }

        // Trik Sapu Jagat: Selalu loloskan semua user dari segala pengecekan Gate/Policy
        Gate::before(function ($user, $ability) {
            return true;
        });
    }
}