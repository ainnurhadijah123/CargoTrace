@extends('layouts.admin')

@section('title', ($user ? 'Edit' : 'Tambah') . ' Pengguna - Admin')
@section('header_title', ($user ? 'Edit Data' : 'Tambah') . ' Pengguna')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-800">{{ $user ? 'Formulir Edit Pengguna' : 'Formulir Pendaftaran Pengguna Baru' }}</h3>
        </div>

        <form action="{{ $user ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
            @csrf
            @if($user)
                @method('PUT')
            @endif

            <div class="p-6 space-y-6">
                <!-- Nama -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user?->name) }}" required placeholder="Contoh: Fahmi Fadillah"
                        class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Alamat Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user?->email) }}" required placeholder="Contoh: budi@email.com"
                        class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    @error('email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password {!! $user ? '<span class="text-xs text-slate-400 font-normal">(Kosongkan jika tidak ingin diubah)</span>' : '<span class="text-red-500">*</span>' !!}</label>
                    <input type="password" name="password" {{ $user ? '' : 'required' }} minlength="8" placeholder="Minimal 8 karakter"
                        class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    @error('password') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Role / Peran -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Hak Akses (Role) <span class="text-red-500">*</span></label>
                    <select name="role" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        @php
                            $currentRole = old('role', is_object($user?->role) ? $user->role->value : $user?->role);
                        @endphp
                        <option value="">-- Pilih Peran --</option>
                        <option value="CUSTOMER" {{ $currentRole === 'CUSTOMER' ? 'selected' : '' }}>CUSTOMER (Pelanggan)</option>
                        <option value="OPERATOR" {{ $currentRole === 'OPERATOR' ? 'selected' : '' }}>OPERATOR (Staf Lapangan)</option>
                        <option value="ADMIN" {{ $currentRole === 'ADMIN' ? 'selected' : '' }}>ADMIN (Administrator Sistem)</option>
                    </select>
                    @error('role') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex justify-end gap-3 p-6 pt-4 border-t border-slate-200 bg-slate-50">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-sm font-semibold transition">Batal</a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">{{ $user ? 'Simpan Perubahan' : 'Daftarkan Pengguna' }}</button>
            </div>
        </form>
    </div>
@endsection