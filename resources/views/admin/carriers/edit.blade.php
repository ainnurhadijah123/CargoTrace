@extends('layouts.admin')

@section('title', 'Edit Carrier - Admin')
@section('header_title', 'Edit Data Armada')

@section('content')
    <div class="max-w-2xl bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">Perbarui Data Armada</h3>
            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">{{ $carrier->code }}</span>
        </div>
        
        <form action="{{ route('admin.carriers.update', $carrier->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Kode Armada -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Kode Armada <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $carrier->code) }}" required
                    class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-700 uppercase transition">
                @error('code')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nama Armada -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Armada <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $carrier->name) }}" required
                    class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-700 transition">
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Info Kontak -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Info Kontak</label>
                <input type="text" name="contact_info" value="{{ old('contact_info', $carrier->contact_info) }}"
                    class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-700 transition">
                @error('contact_info')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center gap-2 mt-4">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $carrier->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 bg-white border-slate-300 rounded focus:ring-blue-500">
                <label for="is_active" class="text-sm font-medium text-slate-700">Aktifkan Armada Ini</label>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex justify-end gap-3 pt-6 border-t border-slate-200">
                <a href="{{ route('admin.carriers.index') }}" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-semibold transition">Batal</a>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection