<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pengiriman Baru - Operator Logistik CargoTrace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        #route-map { height: 340px; z-index: 10; border-radius: 0.75rem; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">

    <!-- Navbar Khusus Operator (Tema Teal) -->
    <nav class="bg-teal-700 text-white p-4 shadow-md flex justify-between items-center sticky top-0 z-30">
        <div class="flex items-center gap-3">
            <a href="{{ route('operator.dashboard') }}" class="font-black text-xl tracking-wide flex items-center gap-2">
                CargoTrace<span class="text-teal-300">.</span>
            </a>
            <span class="text-xs font-bold bg-teal-800 text-teal-200 px-2.5 py-1 rounded-lg uppercase tracking-wider">Logistics Operator</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('operator.dashboard') }}" class="text-xs font-bold text-teal-100 hover:text-white transition flex items-center gap-1.5 bg-teal-800/80 px-3 py-1.5 rounded-lg border border-teal-600">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali ke Dashboard</span>
            </a>
            <a href="{{ route('track.index') }}" target="_blank" class="text-xs font-bold text-teal-100 hover:text-white underline hidden sm:inline">
                Portal Pelacakan Publik &nearr;
            </a>
            <form action="{{ route('logout') }}" method="POST" class="inline m-0">
                @csrf
                <button type="submit" class="bg-teal-800 hover:bg-rose-600 px-3.5 py-1.5 rounded-lg text-xs font-bold transition shadow-sm">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content Form -->
    <main class="flex-grow p-6 md:p-8 max-w-5xl mx-auto w-full">
        
        <!-- Header Halaman -->
        <div class="mb-6 flex flex-col sm:flex-row justify-between sm:items-center gap-4 border-b border-slate-200 pb-5">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-teal-100 text-teal-800 border border-teal-200">DISPATCH CONSOLE</span>
                    <span class="text-xs text-slate-400">• Form Manifest Logistik</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Buat Pengiriman Baru</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Input data manifest kargo, tentukan rute pelayaran/penerbangan pada peta interaktif, dan jadwalkan armada.</p>
            </div>
            <a href="{{ route('operator.dashboard') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                <span>Batal</span>
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-12">
            <form action="{{ route('operator.shipments.store') }}" method="POST">
                @csrf

                <div class="p-6 md:p-8 space-y-8">
                    
                    <!-- Bagian 1: Data Pelanggan & Maskapai -->
                    <div>
                        <h4 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                            <span>1. Informasi Pelanggan & Maskapai Armada</span>
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase font-mono tracking-wider mb-1.5">Pelanggan Pemilik Kargo <span class="text-rose-500">*</span></label>
                                <select name="customer_id" required class="w-full px-4 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 text-sm font-medium transition">
                                    <option value="">-- Pilih Pelanggan --</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} ({{ $customer->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase font-mono tracking-wider mb-1.5">Maskapai / Armada Angkut <span class="text-rose-500">*</span></label>
                                <select name="carrier_id" required class="w-full px-4 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 text-sm font-medium transition">
                                    <option value="">-- Pilih Maskapai / Carrier --</option>
                                    @foreach($carriers as $carrier)
                                        <option value="{{ $carrier->id }}" {{ old('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                            {{ $carrier->name }} ({{ $carrier->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('carrier_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Bagian 2: Rute & Visualisasi Peta Interaktif -->
                    <div>
                        <h4 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                            <span>2. Rute Perjalanan & Jadwal Keberangkatan</span>
                        </h4>
                        
                        <!-- Peta Interaktif Leaflet -->
                        <div class="mb-6 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-bold text-slate-700 uppercase font-mono tracking-wider flex items-center gap-2">
                                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                                    <span>Peta Koridor Global (Klik Pin untuk Memilih)</span>
                                </label>
                                <span class="text-[11px] font-mono font-bold text-teal-700 bg-teal-100 px-2 py-0.5 rounded-full">Interactive Leaflet OSM</span>
                            </div>
                            <div id="route-map" class="w-full shadow-sm border border-slate-300"></div>
                            <p class="mt-2.5 text-xs text-slate-500 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-teal-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                <span>Klik pin pelabuhan di peta, lalu pilih tombol <strong>"Jadikan Asal"</strong> atau <strong>"Jadikan Tujuan"</strong> untuk menggambar jalur otomatis.</span>
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase font-mono tracking-wider mb-1.5">Lokasi Asal (Origin) <span class="text-rose-500">*</span></label>
                                <select name="origin_id" id="origin_select" required class="w-full px-4 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 text-sm font-medium transition">
                                    <option value="">-- Pilih Pelabuhan / Hub Asal --</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('origin_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }} ({{ $location->code }}) - {{ $location->city }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('origin_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase font-mono tracking-wider mb-1.5">Lokasi Tujuan (Destination) <span class="text-rose-500">*</span></label>
                                <select name="destination_id" id="destination_select" required class="w-full px-4 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 text-sm font-medium transition">
                                    <option value="">-- Pilih Pelabuhan / Hub Tujuan --</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('destination_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }} ({{ $location->code }}) - {{ $location->city }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('destination_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase font-mono tracking-wider mb-1.5">Perkiraan Berangkat (Departure) <span class="text-rose-500">*</span></label>
                                <input type="datetime-local" name="expected_departure_at" value="{{ old('expected_departure_at', now()->format('Y-m-d\TH:i')) }}" required class="w-full px-4 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 text-sm font-medium transition">
                                @error('expected_departure_at') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase font-mono tracking-wider mb-1.5">Perkiraan Tiba (Estimated Arrival) <span class="text-rose-500">*</span></label>
                                <input type="datetime-local" name="expected_arrival_at" value="{{ old('expected_arrival_at', now()->addDays(7)->format('Y-m-d\TH:i')) }}" required class="w-full px-4 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 text-sm font-medium transition">
                                @error('expected_arrival_at') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Bagian 3: Detail Kargo & Muatan -->
                    <div>
                        <h4 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                            <span>3. Spesifikasi Kargo & Estimasi Biaya</span>
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase font-mono tracking-wider mb-1.5">Kategori Kargo Utama <span class="text-rose-500">*</span></label>
                                <input type="text" name="cargo_type" value="{{ old('cargo_type') }}" required placeholder="Contoh: Industrial Electronics" class="w-full px-4 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 text-sm font-medium transition">
                                @error('cargo_type') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase font-mono tracking-wider mb-1.5">Total Berat Aktual (Kg) <span class="text-rose-500">*</span></label>
                                <input type="number" step="0.01" name="weight_kg" value="{{ old('weight_kg') }}" required placeholder="Contoh: 4500" class="w-full px-4 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 text-sm font-medium transition">
                                @error('weight_kg') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-700 uppercase font-mono tracking-wider">Total Biaya Pengiriman (Shipping Cost) <span class="text-rose-500">*</span></label>
                                <span id="route_scope_badge" class="text-[10px] font-mono font-bold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200">Pilih Rute Asal & Tujuan</span>
                            </div>
                            <div class="flex relative rounded-xl shadow-sm">
                                <select name="currency" id="currency_select" class="inline-flex items-center px-4 rounded-l-xl border border-r-0 border-slate-300 bg-slate-100 text-slate-700 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-teal-600">
                                    <option value="IDR" {{ old('currency') == 'IDR' ? 'selected' : '' }}>IDR (Rp)</option>
                                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                </select>
                                <input type="number" id="shipping_cost_input" name="shipping_cost" value="{{ old('shipping_cost') }}" required placeholder="Contoh: 125000000" min="0" step="0.01" class="flex-1 min-w-0 block w-full px-4 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 rounded-none rounded-r-xl focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 text-sm font-medium transition">
                            </div>
                            <p id="currency_hint" class="mt-1 text-xs text-slate-500">Rute Internasional otomatis menyarankan USD ($), rute Domestik menyarankan IDR (Rp).</p>
                            @error('shipping_cost') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Item Rincian Pertama -->
                        <div class="p-5 bg-teal-50/50 border border-teal-200/80 rounded-2xl">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-mono font-bold text-teal-900 uppercase">Item Manifest Pertama (UAS Requirement)</span>
                                <span class="text-[10px] font-bold text-teal-700 bg-teal-100 px-2 py-0.5 rounded-full">1 Item Minimum</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Nama / Deskripsi Barang</label>
                                    <input type="text" name="items[0][cargo_type]" value="{{ old('items.0.cargo_type') }}" required placeholder="Contoh: Precision Microcontrollers" class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-600 text-sm transition">
                                    @error('items.0.cargo_type') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Berat Item Ini (Kg)</label>
                                    <input type="number" step="0.01" name="items[0][weight]" value="{{ old('items.0.weight') }}" required placeholder="Contoh: 4500" class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-600 text-sm transition">
                                    @error('items.0.weight') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Footer Action Buttons -->
                <div class="flex items-center justify-between p-6 bg-slate-50 border-t border-slate-200">
                    <a href="{{ route('operator.dashboard') }}" class="px-5 py-2.5 bg-white hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold border border-slate-300 transition shadow-sm">
                        Batal & Kembali
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-teal-600/20 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Buat Resi Pengiriman Baru</span>
                    </button>
                </div>
            </form>
        </div>

    </main>

    <!-- JS Leaflet & Logika Peta Rute -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var locations = @json($locations);
            
            // Inisialisasi Peta
            var map = L.map('route-map').setView([-2.5489, 118.0149], 4);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            var polyline = null;

            locations.forEach(function(loc) {
                if (loc.latitude && loc.longitude) {
                    var marker = L.marker([loc.latitude, loc.longitude]).addTo(map);
                    
                    var popupContent = `
                        <div class="text-center p-1">
                            <strong class="block mb-1 text-sm font-bold text-slate-800">${loc.name} (${loc.code})</strong>
                            <p class="text-xs text-slate-500 mb-2.5">${loc.city}, ${loc.country}</p>
                            <div class="flex gap-1.5 justify-center">
                                <button type="button" onclick="setSelect('origin_select', ${loc.id})" class="px-2.5 py-1 bg-teal-600 hover:bg-teal-700 text-white rounded text-xs font-bold transition">Jadikan Asal</button>
                                <button type="button" onclick="setSelect('destination_select', ${loc.id})" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-bold transition">Jadikan Tujuan</button>
                            </div>
                        </div>
                    `;
                    marker.bindPopup(popupContent);
                }
            });

            window.setSelect = function(selectId, locationId) {
                var selectElement = document.getElementById(selectId);
                if(selectElement) {
                    selectElement.value = locationId;
                    map.closePopup();
                    
                    selectElement.classList.add('ring-2', 'ring-teal-500', 'bg-teal-50');
                    setTimeout(() => selectElement.classList.remove('ring-2', 'ring-teal-500', 'bg-teal-50'), 800);
                    
                    selectElement.dispatchEvent(new Event('change'));
                }
            };

            function drawRouteLine() {
                var originId = document.getElementById('origin_select').value;
                var destId = document.getElementById('destination_select').value;
                var currencySelect = document.getElementById('currency_select');
                var costInput = document.getElementById('shipping_cost_input');
                var routeBadge = document.getElementById('route_scope_badge');
                var hint = document.getElementById('currency_hint');

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
                                routeBadge.className = 'text-[10px] font-mono font-bold text-emerald-800 bg-emerald-100 border border-emerald-300 px-2.5 py-0.5 rounded-full';
                            }
                            if (hint) hint.innerHTML = '<span class="text-emerald-700 font-bold">✓ Terdeteksi Rute Domestik:</span> Sesuai regulasi Bank Indonesia, pengiriman dalam negeri ditagihkan dalam mata uang <strong>Rupiah (IDR)</strong>.';
                        } else {
                            if (currencySelect) currencySelect.value = 'USD';
                            if (costInput && !costInput.value) costInput.placeholder = 'Contoh: 4500 (US Dollar)';
                            if (routeBadge) {
                                routeBadge.textContent = '🌐 Rute Internasional (USD)';
                                routeBadge.className = 'text-[10px] font-mono font-bold text-sky-800 bg-sky-100 border border-sky-300 px-2.5 py-0.5 rounded-full';
                            }
                            if (hint) hint.innerHTML = '<span class="text-sky-700 font-bold">✓ Terdeteksi Rute Internasional:</span> Standar logistik kargo lintas benua (Incoterms) ditagihkan dalam mata uang <strong>US Dollar (USD)</strong>.';
                        }
                    }

                    if (originId !== destId && origin && dest && origin.latitude && dest.latitude) {
                        var latlngs = [
                            [origin.latitude, origin.longitude],
                            [dest.latitude, dest.longitude]
                        ];
                        
                        polyline = L.polyline(latlngs, {
                            color: '#0d9488',
                            weight: 3.5, 
                            dashArray: '8, 8'
                        }).addTo(map);
                        
                        map.fitBounds(polyline.getBounds(), {padding: [50, 50]});
                    }
                }
            }

            document.getElementById('origin_select').addEventListener('change', drawRouteLine);
            document.getElementById('destination_select').addEventListener('change', drawRouteLine);

            drawRouteLine();
        });
    </script>
</body>
</html>
