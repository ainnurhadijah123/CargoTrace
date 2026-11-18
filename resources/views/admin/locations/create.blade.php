@extends('layouts.admin')

@section('title', 'Tambah Lokasi - Admin')
@section('header_title', 'Tambah Lokasi / Gudang Baru')

@section('content')
    <!-- CSS Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="max-w-4xl bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-800">Formulir Data Lokasi</h3>
        </div>
        
        <form action="{{ route('admin.locations.store') }}" method="POST">
            @csrf

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kolom Kiri: Data Teks -->
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kode Lokasi <span class="text-red-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code') }}" required placeholder="Contoh: WH-JKT-01"
                            class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 uppercase transition">
                        @error('code') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lokasi / Gudang <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Gudang Pusat Jakarta"
                            class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kota <span class="text-red-500">*</span></label>
                        <input type="text" name="city" value="{{ old('city') }}" required placeholder="Contoh: Jakarta"
                            class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        @error('city') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Negara <span class="text-red-500">*</span></label>
                        <input type="text" name="country" value="{{ old('country', 'Indonesia') }}" required
                            class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        @error('country') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Kolom Kanan: Peta & Koordinat -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Titik Lokasi di Peta</label>
                        <!-- Container Peta -->
                        <div id="map" class="w-full h-64 rounded-lg border border-slate-300 z-10"></div>
                        <p class="mt-1 text-xs text-slate-500">Klik pada peta atau geser pin merah untuk menentukan koordinat.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Latitude</label>
                            <input type="text" id="latitude" name="latitude" value="{{ old('latitude') }}" readonly
                                class="w-full px-3 py-2 bg-slate-100 border border-slate-300 rounded-lg text-slate-600 focus:outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Longitude</label>
                            <input type="text" id="longitude" name="longitude" value="{{ old('longitude') }}" readonly
                                class="w-full px-3 py-2 bg-slate-100 border border-slate-300 rounded-lg text-slate-600 focus:outline-none text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 p-6 pt-4 border-t border-slate-200 bg-slate-50">
                <a href="{{ route('admin.locations.index') }}" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-sm font-semibold transition">Batal</a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">Simpan Lokasi</button>
            </div>
        </form>
    </div>

    <!-- JS Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Inisialisasi Peta (Koordinat default: Tengah Indonesia)
            var map = L.map('map').setView([-2.5489, 118.0149], 5);

            // 2. Load desain peta dari OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            var marker;

            // 3. Fungsi saat peta diklik
            map.on('click', function(e) {
                var lat = e.latlng.lat;
                var lng = e.latlng.lng;

                // Isi nilai ke input text
                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lng.toFixed(6);

                // Buat atau pindahkan marker
                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng, {draggable: true}).addTo(map);
                    
                    // Update koordinat jika marker digeser manual
                    marker.on('dragend', function(event) {
                        var position = marker.getLatLng();
                        document.getElementById('latitude').value = position.lat.toFixed(6);
                        document.getElementById('longitude').value = position.lng.toFixed(6);
                    });
                }
            });
        });
    </script>
@endsection