<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengiriman - CargoTrace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 400px; z-index: 1; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">
    <div class="min-h-screen flex flex-col">
        <!-- Navbar -->
        <nav class="bg-indigo-600 text-white p-4 shadow-md flex justify-between items-center">
            <div class="font-bold text-xl tracking-wide">CargoTrace</div>
            <a href="{{ route('customer.dashboard') }}" class="text-indigo-100 hover:text-white text-sm font-medium transition">&larr; Kembali ke Dashboard</a>
        </nav>

        <main class="flex-grow p-4 md:p-8 max-w-6xl mx-auto w-full grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Kolom Kiri: Peta dan Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Card Peta Rute -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 bg-slate-50/50">
                        <h2 class="font-bold text-slate-800">Peta Rute Pengiriman</h2>
                    </div>
                    <div id="map"></div>
                </div>

                <!-- Card Informasi Utama -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex justify-between items-start border-b border-slate-100 pb-4 mb-4">
                        <div>
                            <p class="text-sm text-slate-500 font-medium">Nomor Resi</p>
                            <h2 class="text-2xl font-bold text-indigo-700">{{ $shipment->shipment_number }}</h2>
                        </div>
                        <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold border border-amber-200 uppercase">{{ $shipment->status }}</span>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500">Estimasi Tiba (ETA)</p>
                            <p class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($shipment->expected_arrival_at)->format('d M Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Tipe Kargo</p>
                            <p class="font-semibold text-slate-800 capitalize">{{ $shipment->cargo_type }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Berat</p>
                            <p class="font-semibold text-slate-800">{{ $shipment->weight_kg }} Kg</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Armada / Carrier</p>
                            <p class="font-semibold text-slate-800">{{ $shipment->carrier->name ?? 'Belum Ditentukan' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Total Biaya</p>
                            <p class="font-bold text-indigo-700">Rp {{ number_format($shipment->shipping_cost ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Timeline & Notifikasi -->
            <div class="space-y-6">
                <!-- Card Notifikasi / Exception -->
                <div class="bg-rose-50 rounded-xl shadow-sm border border-rose-200 p-5">
                    <h3 class="font-bold text-rose-700 flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Peringatan / Catatan
                    </h3>
                    <p class="text-sm text-rose-600">Saat ini tidak ada kendala (exception) yang dilaporkan. Kargo dalam kondisi aman.</p>
                </div>

                <!-- Card Timeline -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">Timeline Perjalanan</h3>
                    
                    <div class="relative border-l border-slate-200 ml-3 space-y-6">
                        <!-- Checkpoint Dibuat -->
                        <div class="pl-6 relative">
                            <div class="absolute w-3 h-3 bg-indigo-600 rounded-full -left-[6.5px] top-1.5 ring-4 ring-white"></div>
                            <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($shipment->created_at)->format('d M Y, H:i') }}</p>
                            <p class="font-semibold text-sm text-slate-800">Manifes Dibuat ({{ $shipment->origin->code ?? '' }})</p>
                            <p class="text-xs text-slate-600 mt-1">Data pengiriman berhasil didaftarkan di sistem oleh petugas.</p>
                        </div>
                        
                        <!-- Checkpoint Status Terkini -->
                        <div class="pl-6 relative">
                            <div class="absolute w-3 h-3 bg-amber-500 rounded-full -left-[6.5px] top-1.5 ring-4 ring-white"></div>
                            <p class="text-xs text-slate-500">Update Terakhir</p>
                            <p class="font-semibold text-sm text-slate-800">Status: {{ $shipment->status }}</p>
                            <p class="text-xs text-slate-600 mt-1">Menunggu pembaruan titik koordinat oleh Operator Logistik.</p>
                        </div>
                    </div>
                    
                </div>
            </div>
        </main>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Ambil data koordinat dari PHP
        const originLat = parseFloat("{{ $shipment->origin->latitude ?? 0 }}");
        const originLng = parseFloat("{{ $shipment->origin->longitude ?? 0 }}");
        const destLat = parseFloat("{{ $shipment->destination->latitude ?? 0 }}");
        const destLng = parseFloat("{{ $shipment->destination->longitude ?? 0 }}");

        // Inisialisasi peta (Pusatkan di tengah-tengah asal dan tujuan)
        const map = L.map('map').setView([(originLat + destLat)/2, (originLng + destLng)/2], 6);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        // Marker Asal (Biru)
        L.marker([originLat, originLng]).addTo(map)
            .bindPopup("<b>Asal:</b><br>{{ $shipment->origin->name ?? '-' }}");

        // Marker Tujuan (Hijau)
        L.marker([destLat, destLng]).addTo(map)
            .bindPopup("<b>Tujuan:</b><br>{{ $shipment->destination->name ?? '-' }}");

        // Gambar Rute Garis
        const latlngs = [
            [originLat, originLng],
            [destLat, destLng]
        ];
        L.polyline(latlngs, {color: '#4f46e5', weight: 4, dashArray: '10, 10'}).addTo(map);
        
        // Sesuaikan zoom peta agar mencakup seluruh rute
        map.fitBounds(latlngs, {padding: [50, 50]});
    </script>
</body>
</html>