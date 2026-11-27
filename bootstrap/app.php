<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Percayai reverse proxy cloud (Railway, load balancer) agar mendeteksi protokol HTTPS
        $middleware->trustProxies(at: '*');

        // Mendaftarkan alias middleware 'role'
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Kecualikan logout dari validasi CSRF agar tidak terjadi error 419 Page Expired
        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();