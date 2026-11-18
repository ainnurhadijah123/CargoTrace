@extends('layouts.admin')

@section('title', 'Edit Lokasi - Admin')
@section('header_title', 'Edit Data Lokasi / Gudang')

@section('content')
    <!-- CSS Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="max-w-4xl bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-800">Edit Formulir Data Lokasi</h3>
        </div>
        
        <!-- Pastikan action ke update dan method POST -->
        <form action="{{ route('admin.locations.update', $location->id) }}" method="POST">
            @csrf
            @method('PUT') <!-- Wajib untuk proses edit di Laravel -->

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kolom Kiri: Data Teks (Terisi otomatis dengan $location) -->
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kode Lokasi <span class="text-red-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $location->code) }}" required
                            class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 uppercase transition">
                        @error('code') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lokasi / Gudang <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $location->name) }}" required
                            class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kota <span class="text-red-500">*</span></label>
                        <input type="text" name="city" value="{{ old('city', $location->city) }}" required
                            class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        @error('city') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Negara <span class="text-red-500">*</span></label>
                        <input type="text" name="country" value="{{ old('country', $location->country) }}" required
                            class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        @error('country') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Kolom Kanan: Peta -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Ubah Titik Lokasi di Peta</label>
                        <div id="map" class="w-full h-64 rounded-lg border border-slate-300 z-10"></div>
                        <p class="mt-1 text-xs text-slate-500">Klik area baru pada peta atau geser pin merah untuk mengubah koordinat.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Latitude</label>
                            <input type="text" id="latitude" name="latitude" value="{{ old('latitude', $location->latitude) }}" readonly
                                class="w-full px-3 py-2 bg-slate-100 border border-slate-300 rounded-lg text-slate-600 focus:outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Longitude</label>
                            <input type="text" id="longitude" name="longitude" value="{{ old('longitude', $location->longitude) }}" readonly
                                class="w-full px-3 py-2 bg-slate-100 border border-slate-300 rounded-lg text-slate-600 focus:outline-none text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 p-6 pt-4 border-t border-slate-200 bg-slate-50">
                <a href="{{ route('admin.locations.index') }}" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-sm font-semibold transition">Batal</a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <!-- JS Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Ambil koordinat lama dari database, jika belum ada set ke tengah Indonesia
            var savedLat = {{ $location->latitude ?? 'null' }};
            var savedLng = {{ $location->longitude ?? 'null' }};
            
            var startLat = savedLat !== null ? savedLat : -2.5489;
            var startLng = savedLng !== null ? savedLng : 118.0149;
            var startZoom = savedLat !== null ? 14 : 5; // Zoom lebih dekat jika koordinat sudah ada

            var map = L.map('map').setView([startLat, startLng], startZoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            var marker = null;

            // Jika koordinat tersimpan di DB, pasang marker
            if (savedLat !== null && savedLng !== null) {
                marker = L.marker([savedLat, savedLng], {draggable: true}).addTo(map);
                
                // Update input saat marker digeser
                marker.on('dragend', function(event) {
                    var position = marker.getLatLng();
                    document.getElementById('latitude').value = position.lat.toFixed(6);
                    document.getElementById('longitude').value = position.lng.toFixed(6);
                });
            }

            // Update input & pindahkan marker saat peta diklik
            map.on('click', function(e) {
                var lat = e.latlng.lat;
                var lng = e.latlng.lng;

                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lng.toFixed(6);

                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng, {draggable: true}).addTo(map);
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