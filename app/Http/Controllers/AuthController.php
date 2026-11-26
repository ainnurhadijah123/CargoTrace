<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;               
use App\Models\Shipment;
use App\Enums\UserRole;

class AuthController extends Controller
{
    // Menampilkan form login
    public function showLoginForm(Request $request)
    {
        if ($request->filled('resi')) {
            session(['intended_tracking_resi' => $request->resi]);
        }

        return view('auth.login');
    }

    // Memproses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            $role = $user->role;
            $userRoleVal = is_object($role) ? $role->value : $role;

            $intendedResi = $request->input('intended_resi') ?: session('intended_tracking_resi');

            if ($intendedResi) {
                session()->forget('intended_tracking_resi');
                $shipment = Shipment::with('customer')->where('shipment_number', $intendedResi)->first();

                if ($shipment) {
                    $isStaff = in_array($userRoleVal, ['ADMIN', 'OPERATOR']);
                    $isOwner = ($shipment->customer_id === $user->id);

                    if ($isStaff || $isOwner) {
                        return redirect()->route('track.index', ['resi' => $shipment->shipment_number])
                            ->with('success', "Selamat datang kembali, {$user->name}! Rincian kargo {$shipment->shipment_number} berhasil dibuka.");
                    } else {
                        return redirect()->route('customer.dashboard')
                            ->with('warning', "🔒 Anda masuk sebagai {$user->name}. Pengiriman {$intendedResi} terdaftar atas nama pelanggan lain ({$shipment->customer?->name}), sehingga Anda dialihkan ke portal kargo Anda sendiri.");
                    }
                }
            }

            // Redirect berdasarkan Enum UserRole ke rute dashboard masing-masing
            return match ($role) {
                UserRole::ADMIN => redirect()->route('admin.dashboard'),
                UserRole::OPERATOR => redirect()->route('operator.dashboard'),
                default => redirect()->route('customer.dashboard'),
            };
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    // Menampilkan form register
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Memproses register akun baru
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => UserRole::CUSTOMER,
        ]);

        Auth::login($user);

        // Arahkan ke dashboard customer
        return redirect()->route('customer.dashboard');
    }

    // Memproses logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}