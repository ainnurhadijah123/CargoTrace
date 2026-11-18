<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengiriman - Operator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style> #map { height: 400px; z-index: 1; } </style>
</head>
<body class="bg-slate-50 text-slate-800">
    <div class="min-h-screen flex flex-col">
        <!-- Navbar -->
        <nav class="bg-teal-700 text-white p-4 shadow-md flex justify-between items-center">
            <div class="font-bold text-xl tracking-wide">CargoTrace <span class="text-sm font-normal bg-teal-800 px-2 py-1 rounded ml-2">Operator</span></div>
            <a href="{{ route('operator.dashboard') }}" class="text-teal-100 hover:text-white text-sm font-medium transition">&larr; Kembali ke Dashboard</a>
        </nav>

        <main class="flex-grow p-4 md:p-8 max-w-6xl mx-auto w-full grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom Kiri: Peta dan Info -->
            <div class="lg:col-span-2 space-y-6">
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
                            <h2 class="text-2xl font-bold text-teal-700">{{ $shipment->shipment_number }}</h2>
                        </div>
                        <div class="text-right">
                            <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-xs font-bold border border-slate-200 uppercase mb-2 inline-block">{{ $shipment->status }}</span>
                            <br>
                            <a href="{{ route('operator.shipments.edit', $shipment->shipment_number) }}" class="text-xs text-teal-600 font-semibold hover:underline">Ubah Status &rarr;</a>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500">Pelanggan</p>
                            <p class="font-semibold text-slate-800">{{ $shipment->customer->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Estimasi Tiba</p>
                            <p class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($shipment->expected_arrival_at)->format('d M Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Kargo & Berat</p>
                            <p class="font-semibold text-slate-800 capitalize">{{ $shipment->cargo_type }} ({{ $shipment->weight_kg }} Kg)</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Armada / Carrier</p>
                            <p class="font-semibold text-slate-800">{{ $shipment->carrier->name ?? 'Belum Ditentukan' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Total Biaya</p>
                            <p class="font-bold text-teal-700">Rp {{ number_format($shipment->shipping_cost ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Timeline -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">Timeline Perjalanan</h3>
                    <div class="relative border-l border-slate-200 ml-3 space-y-6">
                        <div class="pl-6 relative">
                            <div class="absolute w-3 h-3 bg-teal-600 rounded-full -left-[6.5px] top-1.5 ring-4 ring-white"></div>
                            <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($shipment->created_at)->format('d M Y, H:i') }}</p>
                            <p class="font-semibold text-sm text-slate-800">Manifes Dibuat ({{ $shipment->origin->code ?? '' }})</p>
                        </div>
                        
                        <div class="pl-6 relative">
                            <div class="absolute w-3 h-3 bg-amber-500 rounded-full -left-[6.5px] top-1.5 ring-4 ring-white"></div>
                            <p class="text-xs text-slate-500">Status Terkini</p>
                            <p class="font-semibold text-sm text-slate-800">{{ $shipment->status }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const originLat = parseFloat("{{ $shipment->origin->latitude ?? 0 }}");
        const originLng = parseFloat("{{ $shipment->origin->longitude ?? 0 }}");
        const destLat = parseFloat("{{ $shipment->destination->latitude ?? 0 }}");
        const destLng = parseFloat("{{ $shipment->destination->longitude ?? 0 }}");

        const map = L.map('map').setView([(originLat + destLat)/2, (originLng + destLng)/2], 6);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        L.marker([originLat, originLng]).addTo(map).bindPopup("<b>Asal:</b><br>{{ $shipment->origin->name ?? '-' }}");
        L.marker([destLat, destLng]).addTo(map).bindPopup("<b>Tujuan:</b><br>{{ $shipment->destination->name ?? '-' }}");

        const latlngs = [[originLat, originLng], [destLat, destLng]];
        L.polyline(latlngs, {color: '#0f766e', weight: 4, dashArray: '10, 10'}).addTo(map);
        map.fitBounds(latlngs, {padding: [50, 50]});
    </script>
</body>
</html>