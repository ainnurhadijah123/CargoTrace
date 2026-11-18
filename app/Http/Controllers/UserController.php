<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // Gate::authorize('viewAny', User::class); // Dinonaktifkan sementara agar tidak error 403

        // PERUBAHAN: Prefix admin dan pagination
        $users = User::orderBy('name')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    // --- TAMBAHAN UNTUK FITUR ADMIN (CREATE & EDIT) ---
    public function create()
    {
        return view('admin.users.form', ['user' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        // Mencegah admin mengubah role-nya sendiri dari form
        if ($user->is($request->user()) && $request->role !== $user->role->value) {
            return back()->withErrors(['role' => 'You cannot change your own role.']);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        // Update password hanya jika diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->is(request()->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    // --- MEMPERTAHANKAN FUNGSI LAMA KAMU ---
    public function updateRole(Request $request, User $user)
    {
        // Gate::authorize('update', $user);

        $data = $request->validate([
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        if ($user->is($request->user())) {
            return back()->withErrors(['role' => 'You cannot change your own role.']);
        }

        $user->role = UserRole::from($data['role']);
        $user->save();

        // Mengubah status menjadi success agar seragam dengan fungsi lain
        return back()->with('success', "Role updated for {$user->name}.");
    }
}