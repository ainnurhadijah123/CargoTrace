@extends('layouts.admin')

@section('title', ($shipment ? 'Edit' : 'Buat') . ' Resi Pengiriman - Admin')
@section('header_title', ($shipment ? 'Edit' : 'Buat') . ' Resi Pengiriman')

@section('content')
    <!-- CSS Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="max-w-4xl bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-10">
        <div class="p-6 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-800">{{ $shipment ? 'Formulir Edit Resi' : 'Formulir Pembuatan Resi Baru' }}</h3>
        </div>

        <form action="{{ $shipment ? (auth()->user()->isAdmin() ? route('admin.shipments.update', $shipment) : route('operator.shipments.update', $shipment->shipment_number)) : (auth()->user()->isAdmin() ? route('admin.shipments.store') : route('operator.shipments.store')) }}" method="POST">
            @csrf
            @if($shipment)
                @method('PUT')
            @endif

            <div class="p-6 space-y-8">
                <!-- Data Pelanggan & Maskapai -->
                <div>
                    <h4 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Informasi Utama</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Pelanggan <span class="text-red-500">*</span></label>
                            <select name="customer_id" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id', $shipment?->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Maskapai / Armada <span class="text-red-500">*</span></label>
                            <select name="carrier_id" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                <option value="">-- Pilih Maskapai --</option>
                                @foreach($carriers as $carrier)
                                    <option value="{{ $carrier->id }}" {{ old('carrier_id', $shipment?->carrier_id) == $carrier->id ? 'selected' : '' }}>
                                        {{ $carrier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('carrier_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Rute & Jadwal Pengiriman -->
                <div>
                    <h4 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Rute & Jadwal</h4>
                    
                    <!-- AREA PETA INTERAKTIF BARU -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Visualisasi Rute (Pilih Langsung dari Peta)</label>
                        <div id="route-map" class="w-full h-80 rounded-lg border border-slate-300 z-10"></div>
                        <p class="mt-2 text-xs text-slate-500 flex items-center gap-1">
                            <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            Klik pin lokasi pada peta, lalu tekan tombol untuk menetapkannya sebagai Asal atau Tujuan.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Lokasi Asal (Origin) <span class="text-red-500">*</span></label>
                            <select name="origin_id" id="origin_select" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                <option value="">-- Pilih Asal --</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ old('origin_id', $shipment?->origin_id) == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }} ({{ $location->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('origin_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Lokasi Tujuan (Destination) <span class="text-red-500">*</span></label>
                            <select name="destination_id" id="destination_select" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                <option value="">-- Pilih Tujuan --</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ old('destination_id', $shipment?->destination_id) == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }} ({{ $location->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('destination_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Perkiraan Berangkat <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="expected_departure_at" value="{{ old('expected_departure_at', $shipment?->expected_departure_at?->format('Y-m-d\TH:i')) }}" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                            @error('expected_departure_at') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Perkiraan Tiba <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="expected_arrival_at" value="{{ old('expected_arrival_at', $shipment?->expected_arrival_at?->format('Y-m-d\TH:i')) }}" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                            @error('expected_arrival_at') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Detail Kargo Global & Item -->
                <div>
                    <h4 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Detail Kargo & Barang</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Jenis Kargo (Keseluruhan) <span class="text-red-500">*</span></label>
                            <input type="text" name="cargo_type" value="{{ old('cargo_type', $shipment?->cargo_type) }}" required placeholder="Contoh: Elektronik" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                            @error('cargo_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Total Berat Aktual (Kg) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="weight_kg" value="{{ old('weight_kg', $shipment?->weight_kg) }}" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                            @error('weight_kg') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-slate-700">Total Biaya Pengiriman <span class="text-red-500">*</span></label>
                            <span id="route_scope_badge_admin" class="text-[10px] font-mono font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">Pilih Rute Asal & Tujuan</span>
                        </div>
                        <div class="flex relative rounded-md shadow-sm">
                            <!-- Dropdown Pilih Mata Uang -->
                            <select name="currency" id="admin_currency_select" class="inline-flex items-center px-4 rounded-l-md border border-r-0 border-slate-300 bg-slate-50 text-slate-700 font-bold sm:text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="IDR" {{ old('currency', $shipment?->currency) == 'IDR' ? 'selected' : '' }}>IDR (Rp)</option>
                                <option value="USD" {{ old('currency', $shipment?->currency) == 'USD' ? 'selected' : '' }}>USD ($)</option>
                            </select>
                            
                            <!-- Input Nominal -->
                            <input type="number" name="shipping_cost" id="admin_shipping_cost_input" value="{{ old('shipping_cost', $shipment?->shipping_cost ?? '') }}" required placeholder="Contoh: 150000 atau 50" min="0" step="0.01" class="flex-1 min-w-0 block w-full px-4 py-2 bg-white border border-slate-300 rounded-none rounded-r-md focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        </div>
                        <p id="admin_currency_hint" class="mt-1 text-xs text-slate-500">Rute Internasional otomatis menyarankan USD ($), rute Domestik menyarankan IDR (Rp).</p>
                        @error('shipping_cost') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <!-- Item Rincian Box -->
                    <div class="p-5 bg-slate-50 border border-slate-200 rounded-lg">
                        <p class="text-sm font-bold text-slate-700 mb-3">Rincian Barang Pertama</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Jenis Barang (Detail Item)</label>
                                <input type="text" name="items[0][cargo_type]" value="{{ old('items.0.cargo_type', $shipment?->items->first()?->cargo_type) }}" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500 text-sm transition">
                                @error('items.0.cargo_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Berat Item Ini (Kg)</label>
                                <input type="number" step="0.01" name="items[0][weight]" value="{{ old('items.0.weight', $shipment?->items->first()?->weight) }}" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500 text-sm transition">
                                @error('items.0.weight') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 p-6 pt-4 border-t border-slate-200 bg-slate-50">
                <a href="{{ auth()->user()->isAdmin() ? route('admin.shipments.index') : route('operator.dashboard') }}" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-sm font-semibold transition">Batal</a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">{{ $shipment ? 'Simpan Perubahan' : 'Buat Resi Baru' }}</button>
            </div>
        </form>
    </div>

    <!-- JS Leaflet & Logika Peta Rute -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Ambil data lokasi (gudang) dari database (diubah ke JSON)
            var locations = @json($locations);
            
            // Inisialisasi Peta
            var map = L.map('route-map').setView([-2.5489, 118.0149], 5);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            var polyline = null; // Garis rute

            // Loop untuk menaruh pin semua lokasi di peta
            locations.forEach(function(loc) {
                if (loc.latitude && loc.longitude) {
                    var marker = L.marker([loc.latitude, loc.longitude]).addTo(map);
                    
                    // Konten popup saat pin diklik
                    var popupContent = `
                        <div class="text-center p-1">
                            <strong class="block mb-2 text-sm">${loc.name} (${loc.code})</strong>
                            <p class="text-xs text-gray-500 mb-3">${loc.city}</p>
                            <div class="flex gap-2 justify-center">
                                <button type="button" onclick="setSelect('origin_select', ${loc.id})" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs transition">Jadikan Asal</button>
                                <button type="button" onclick="setSelect('destination_select', ${loc.id})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs transition">Jadikan Tujuan</button>
                            </div>
                        </div>
                    `;
                    marker.bindPopup(popupContent);
                }
            });

            // Fungsi global agar tombol di popup bisa dipanggil
            window.setSelect = function(selectId, locationId) {
                var selectElement = document.getElementById(selectId);
                if(selectElement) {
                    selectElement.value = locationId; // Ubah nilai dropdown
                    map.closePopup(); // Tutup popup di peta
                    
                    // Efek warna hijau sejenak untuk memberitahu dropdown berhasil diubah
                    selectElement.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-50');
                    setTimeout(() => selectElement.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-50'), 800);
                    
                    // Panggil fungsi gambar garis rute
                    selectElement.dispatchEvent(new Event('change'));
                }
            };

            // Fungsi untuk menggambar garis antara Asal dan Tujuan & Deteksi Otomatis Mata Uang
            function drawRouteLine() {
                var originId = document.getElementById('origin_select').value;
                var destId = document.getElementById('destination_select').value;
                var currencySelect = document.getElementById('admin_currency_select');
                var costInput = document.getElementById('admin_shipping_cost_input');
                var routeBadge = document.getElementById('route_scope_badge_admin');
                var hint = document.getElementById('admin_currency_hint');

                // Hapus garis yang lama jika ada
                if (polyline) {
                    map.removeLayer(polyline);
                }

                if (originId && destId) {
                    var origin = locations.find(l => l.id == originId);
                    var dest = locations.find(l => l.id == destId);

                    // Smart Auto-detection: Domestic (IDR) vs International (USD)
                    if (origin && dest && origin.country && dest.country) {
                        var isDomestic = (origin.country.toLowerCase() === 'indonesia' && dest.country.toLowerCase() === 'indonesia');
                        if (isDomestic) {
                            if (currencySelect) currencySelect.value = 'IDR';
                            if (costInput && !costInput.value) costInput.placeholder = 'Contoh: 25000000 (Rupiah)';
                            if (routeBadge) {
                                routeBadge.textContent = '🇮🇩 Rute Domestik (IDR)';
                                routeBadge.className = 'text-[10px] font-mono font-bold text-emerald-800 bg-emerald-100 border border-emerald-300 px-2 py-0.5 rounded-full';
                            }
                            if (hint) hint.innerHTML = '<span class="text-emerald-700 font-bold">✓ Terdeteksi Rute Domestik:</span> Pengiriman dalam negeri ditagihkan dalam mata uang <strong>Rupiah (IDR)</strong>.';
                        } else {
                            if (currencySelect) currencySelect.value = 'USD';
                            if (costInput && !costInput.value) costInput.placeholder = 'Contoh: 4500 (US Dollar)';
                            if (routeBadge) {
                                routeBadge.textContent = '🌐 Rute Internasional (USD)';
                                routeBadge.className = 'text-[10px] font-mono font-bold text-sky-800 bg-sky-100 border border-sky-300 px-2 py-0.5 rounded-full';
                            }
                            if (hint) hint.innerHTML = '<span class="text-sky-700 font-bold">✓ Terdeteksi Rute Internasional:</span> Standar logistik kargo internasional ditagihkan dalam mata uang <strong>US Dollar (USD)</strong>.';
                        }
                    }

                    if (originId !== destId && origin && dest && origin.latitude && dest.latitude) {
                        var latlngs = [
                            [origin.latitude, origin.longitude],
                            [dest.latitude, dest.longitude]
                        ];
                        
                        // Gambar garis putus-putus
                        polyline = L.polyline(latlngs, {
                            color: '#3b82f6', // Biru
                            weight: 4, 
                            dashArray: '10, 10'
                        }).addTo(map);
                        
                        // Sesuaikan zoom peta agar kedua lokasi terlihat
                        map.fitBounds(polyline.getBounds(), {padding: [50, 50]});
                    }
                }
            }

            // Dengarkan perubahan pada dropdown Asal dan Tujuan
            document.getElementById('origin_select').addEventListener('change', drawRouteLine);
            document.getElementById('destination_select').addEventListener('change', drawRouteLine);

            // Gambar rute otomatis saat halaman Edit dimuat (jika data sudah ada)
            drawRouteLine();
        });
    </script>
@endsection