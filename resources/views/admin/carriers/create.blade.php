@extends('layouts.admin')

@section('title', 'Tambah Carrier - Admin')
@section('header_title', 'Tambah Armada Baru')

@section('content')
    <div class="max-w-2xl bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-800">Formulir Data Armada</h3>
        </div>
        
        <form action="{{ route('admin.carriers.store') }}" method="POST">
            @csrf

            <!-- Wrapper untuk form input agar ada jarak pinggir (padding) -->
            <div class="p-6 space-y-5">
                <!-- Kode Armada -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kode Armada <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" required placeholder="Contoh: GARUDA, JNE, SICEPAT"
                        class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-700 uppercase transition">
                    @error('code')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-slate-500">Gunakan singkatan unik tanpa spasi.</p>
                </div>

                <!-- Nama Armada -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Armada <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Garuda Indonesia Cargo"
                        class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-700 transition">
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info Kontak -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Info Kontak</label>
                    <input type="text" name="contact_info" value="{{ old('contact_info') }}" placeholder="No. HP / Email / Telepon Kantor"
                        class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-700 transition">
                    @error('contact_info')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Aktif -->
                <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 bg-white border-slate-300 rounded focus:ring-blue-500">
                    <label for="is_active" class="text-sm font-medium text-slate-700">Aktifkan Armada Ini</label>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex justify-end gap-3 p-6 pt-4 border-t border-slate-200 bg-slate-50">
                <!-- PERUBAHAN: admin.carriers.index -->
                <a href="{{ route('admin.carriers.index') }}" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-sm font-semibold transition">Batal</a>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">Simpan Data</button>
            </div>
        </form>
    </div>
@endsection