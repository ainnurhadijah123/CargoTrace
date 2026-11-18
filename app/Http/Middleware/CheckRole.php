<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Tambahkan baris ini
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $userRole = is_object($user->role) && isset($user->role->value) ? $user->role->value : $user->role;

        // Cek apakah role user ada di dalam daftar role yang diizinkan
        if (!in_array($userRole, $roles)) {
            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk melihat halaman ini.');
        }

        return $next($request);
    }
}