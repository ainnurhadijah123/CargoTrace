<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail & Timeline Resi {{ $shipment->shipment_number }} - GoTrace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 380px; z-index: 1; border-radius: 0.75rem; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">
    <!-- Navbar -->
    <nav class="bg-indigo-600 text-white p-4 shadow-md flex justify-between items-center sticky top-0 z-30">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.dashboard') }}" class="font-black text-xl tracking-wide flex items-center gap-1.5">
                GoTrace<span class="text-indigo-300">.</span>
            </a>
            <span class="text-xs font-bold bg-indigo-700 text-indigo-100 px-2.5 py-1 rounded-lg uppercase tracking-wider">Customer Portal</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('customer.dashboard') }}" class="text-xs font-bold text-indigo-100 hover:text-white transition flex items-center gap-1">
                &larr; Kembali ke Dashboard
            </a>
            <form action="{{ route('logout') }}" method="POST" class="inline m-0">
                @csrf
                <button type="submit" class="bg-indigo-700 hover:bg-rose-600 px-3.5 py-1.5 rounded-lg text-xs font-bold transition shadow-sm">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow p-4 md:p-8 max-w-6xl mx-auto w-full">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight">
                        Resi: <span class="text-indigo-600">{{ $shipment->shipment_number }}</span>
                    </h1>
                    @php
                        $statusBadge = match($shipment->status) {
                            'DELIVERED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'IN_TRANSIT' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                            'EXCEPTION' => 'bg-rose-100 text-rose-800 border-rose-200 animate-pulse',
                            'BOOKED' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'PICKED_UP' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-black border uppercase {{ $statusBadge }}">
                        {{ $shipment->status }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Didaftarkan pada {{ $shipment->created_at->format('d M Y, H:i') }} &bull; Carrier: {{ $shipment->carrier->name ?? 'Armada Terdaftar' }}
                </p>
            </div>
            <a href="{{ route('customer.dashboard') }}" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold transition shadow-sm self-start sm:self-auto">
                &larr; Semua Kiriman Saya
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Kolom Kiri: Peta, Cuaca & Info (2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Card Peta Rute -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 bg-slate-50/60 flex justify-between items-center">
                        <h2 class="font-bold text-slate-800 text-sm">Peta Geografis Jalur Pengiriman (GIS)</h2>
                        <span class="text-xs font-semibold text-slate-500">OpenStreetMap + Leaflet.js</span>
                    </div>
                    <div class="p-4">
                        <div id="map"></div>
                    </div>
                </div>

                <!-- Live Weather Card (Open-Meteo Integration) -->
                @if(isset($weather))
                    <div class="bg-gradient-to-r from-sky-50 to-indigo-50 border border-sky-200 rounded-2xl p-5 shadow-sm">
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-white text-sky-600 rounded-xl shadow-sm border border-sky-100">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-sky-600 uppercase tracking-wider block">Live Weather Telemetry (Open-Meteo API)</span>
                                    <h4 class="text-base font-bold text-slate-800">{{ $weather['condition'] ?? 'Cerah Berawan' }}</h4>
                                    <p class="text-xs text-slate-500">Kondisi cuaca terkini di checkpoint kargo Anda.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 text-xs font-bold">
                                <div class="bg-white px-3 py-2 rounded-xl border border-sky-100 text-center">
                                    <span class="text-slate-400 block text-[10px] uppercase">Suhu</span>
                                    <span class="text-slate-800 text-sm">{{ $weather['temperature'] ?? 28 }}&deg;C</span>
                                </div>
                                <div class="bg-white px-3 py-2 rounded-xl border border-sky-100 text-center">
                                    <span class="text-slate-400 block text-[10px] uppercase">Kecepatan Angin</span>
                                    <span class="text-slate-800 text-sm">{{ $weather['wind_speed'] ?? 10 }} km/h</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Card Informasi Utama & ETA -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-slate-800 text-sm pb-3 border-b border-slate-100 mb-4">Informasi Kargo & Spesifikasi</h3>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-5 text-xs">
                        <div>
                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Rute Asal (Origin)</span>
                            <p class="font-bold text-slate-800 text-sm mt-0.5">{{ $shipment->origin->name ?? '-' }}</p>
                            <p class="text-slate-500">{{ $shipment->origin->city ?? '' }}, {{ $shipment->origin->country ?? '' }}</p>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Rute Tujuan (Destination)</span>
                            <p class="font-bold text-slate-800 text-sm mt-0.5">{{ $shipment->destination->name ?? '-' }}</p>
                            <p class="text-slate-500">{{ $shipment->destination->city ?? '' }}, {{ $shipment->destination->country ?? '' }}</p>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Target Estimasi Tiba (ETA)</span>
                            <p class="font-bold text-indigo-700 text-sm mt-0.5">
                                {{ $shipment->expected_arrival_at ? \Carbon\Carbon::parse($shipment->expected_arrival_at)->format('d M Y, H:i') : 'Pending' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Tipe Kargo</span>
                            <p class="font-semibold text-slate-800 capitalize mt-0.5">{{ $shipment->cargo_type }}</p>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Total Berat</span>
                            <p class="font-semibold text-slate-800 mt-0.5">{{ number_format($shipment->weight_kg, 1) }} Kg</p>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Biaya Pengiriman</span>
                            <p class="font-bold text-emerald-700 text-sm mt-0.5">{{ $shipment->formatted_cost }}</p>
                        </div>
                    </div>

                    <!-- ETA Delay Explanation Box -->
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Status Keterlambatan Deterministik</span>
                        @if(isset($delay) && $delay['is_delayed'])
                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs">
                                <div class="flex items-center gap-1.5 font-bold mb-1">
                                    <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                    <span>Kargo Mengalami Keterlambatan: {{ $delay['delay_hours'] }} Jam (Tingkat: {{ $delay['severity'] }})</span>
                                </div>
                                <p class="text-[11px] text-rose-700">{{ $delay['message'] }}</p>
                            </div>
                        @else
                            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                <span class="font-bold">Pengiriman berjalan sesuai jadwal (On Schedule). Tidak ada keterlambatan.</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Cargo Items Table -->
                @if($shipment->items && $shipment->items->count() > 0)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-4 border-b border-slate-200 bg-slate-50/60">
                            <h3 class="font-bold text-slate-800 text-sm">Rincian Barang Kargo</h3>
                        </div>
                        <div class="p-4 overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="p-2.5">Deskripsi Item</th>
                                        <th class="p-2.5">Kategori</th>
                                        <th class="p-2.5 text-center">Jumlah</th>
                                        <th class="p-2.5 text-right">Berat (kg)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($shipment->items as $item)
                                        <tr>
                                            <td class="p-2.5 font-semibold text-slate-800">{{ $item->description }}</td>
                                            <td class="p-2.5 text-slate-600">{{ $item->cargo_type }}</td>
                                            <td class="p-2.5 text-center font-bold">{{ $item->quantity }}</td>
                                            <td class="p-2.5 text-right font-bold">{{ number_format($item->weight_kg, 1) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Kolom Kanan: Exception Alerts & Chronological Timeline (1 col) -->
            <div class="space-y-6">
                <!-- Card Notifikasi / Exception Relevan (Slide 05 & Slide 08) -->
                @php
                    $activeExceptions = $shipment->exceptions->whereIn('status', ['OPEN', 'INVESTIGATING']);
                    $resolvedExceptions = $shipment->exceptions->where('status', 'RESOLVED');
                @endphp

                @if($activeExceptions->count() > 0)
                    <div class="bg-rose-50 rounded-2xl shadow-sm border border-rose-200 p-5">
                        <h3 class="font-bold text-rose-800 flex items-center gap-2 mb-2 text-sm">
                            <svg class="w-5 h-5 text-rose-600 flex-shrink-0 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Peringatan Kendala Operasional
                        </h3>
                        <div class="space-y-3">
                            @foreach($activeExceptions as $exc)
                                <div class="bg-white p-3 rounded-xl border border-rose-200 text-xs">
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="font-bold text-rose-800">{{ str_replace('_', ' ', $exc->type) }}</span>
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black bg-rose-100 text-rose-800 border border-rose-200 uppercase">{{ $exc->severity }}</span>
                                    </div>
                                    <p class="text-[11px] text-slate-700">{{ $exc->description }}</p>
                                    <span class="text-[10px] text-slate-400 mt-1 block">Waktu Deteksi: {{ \Carbon\Carbon::parse($exc->detected_at)->format('d M Y H:i') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($resolvedExceptions->count() > 0)
                    <div class="bg-emerald-50 rounded-2xl shadow-sm border border-emerald-200 p-4 text-xs">
                        <div class="flex items-center gap-2 font-bold text-emerald-800 mb-1">
                            <span>✅ Kendala Sebelumnya Telah Diselesaikan</span>
                        </div>
                        <p class="text-emerald-700 text-[11px]">Semua exception pada pengiriman ini telah diselesaikan oleh tim operasional.</p>
                    </div>
                @else
                    <div class="bg-emerald-50 rounded-2xl shadow-sm border border-emerald-200 p-4 text-xs">
                        <div class="flex items-center gap-2 font-bold text-emerald-800 mb-1">
                            <span>✅ Kondisi Perjalanan Aman</span>
                        </div>
                        <p class="text-emerald-700 text-[11px]">Tidak ada kendala (exception) yang dilaporkan. Kargo dalam penanganan optimal.</p>
                    </div>
                @endif

                <!-- Card Timeline Kronologis (Slide 07) -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4 text-sm flex justify-between items-center">
                        <span>Timeline Perjalanan Kargo</span>
                        <span class="text-xs text-slate-400 font-normal">Riwayat Lengkap</span>
                    </h3>
                    
                    <div class="relative border-l-2 border-slate-200 ml-3 space-y-6">
                        @forelse($shipment->chronologicalEvents as $ev)
                            @php
                                $isExc = $ev->status === 'EXCEPTION';
                                $isDel = $ev->status === 'DELIVERED';
                                $dotColor = $isExc ? 'bg-rose-600' : ($isDel ? 'bg-emerald-600' : 'bg-indigo-600');
                            @endphp
                            <div class="pl-6 relative">
                                <div class="absolute w-3.5 h-3.5 {{ $dotColor }} rounded-full -left-[7.5px] top-1 ring-4 ring-white shadow-sm"></div>
                                <div class="flex justify-between items-start">
                                    <span class="text-xs font-bold {{ $isExc ? 'text-rose-700' : 'text-slate-800' }}">
                                        {{ $ev->status }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">
                                        {{ \Carbon\Carbon::parse($ev->event_time)->format('d M Y, H:i') }}
                                    </span>
                                </div>
                                @if($ev->location)
                                    <p class="text-[11px] font-semibold text-slate-600 mt-0.5">
                                        📍 {{ $ev->location->name }} ({{ $ev->location->city }})
                                    </p>
                                @endif
                                <p class="text-xs text-slate-600 mt-1 {{ $isExc ? 'bg-rose-50 p-2 rounded-lg border border-rose-100 text-rose-800' : '' }}">
                                    {{ $ev->description }}
                                </p>
                            </div>
                        @empty
                            <p class="pl-6 text-xs text-slate-400 italic">Belum ada riwayat pergerakan kargo.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 p-4 text-center text-xs text-slate-500 mt-8">
        &copy; {{ date('Y') }} GoTrace &bull; Enterprise Global Shipment Visibility Platform
    </footer>

    <!-- Leaflet JS & Map Script -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const originLat = parseFloat("{{ $shipment->origin->latitude ?? 0 }}");
            const originLng = parseFloat("{{ $shipment->origin->longitude ?? 0 }}");
            const destLat = parseFloat("{{ $shipment->destination->latitude ?? 0 }}");
            const destLng = parseFloat("{{ $shipment->destination->longitude ?? 0 }}");

            const map = L.map('map').setView([(originLat + destLat)/2, (originLng + destLng)/2], 4);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            const points = [];

            // Origin Marker
            if (originLat && originLng) {
                const originMarker = L.marker([originLat, originLng]).addTo(map);
                originMarker.bindPopup(`<b>📍 Asal: {{ $shipment->origin->name }}</b><br>{{ $shipment->origin->city }}, {{ $shipment->origin->country }}`);
                points.push([originLat, originLng]);
            }

            // Tracking Checkpoints Waypoints
            @foreach($shipment->chronologicalEvents as $event)
                @if($event->latitude && $event->longitude)
                    (function() {
                        const lat = parseFloat("{{ $event->latitude }}");
                        const lng = parseFloat("{{ $event->longitude }}");
                        points.push([lat, lng]);

                        const isException = "{{ $event->status }}" === 'EXCEPTION';
                        const marker = L.circleMarker([lat, lng], {
                            radius: 7,
                            fillColor: isException ? '#e11d48' : '#4f46e5',
                            color: '#ffffff',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.9
                        }).addTo(map);

                        marker.bindPopup(`
                            <div class="text-xs">
                                <b class="${isException ? 'text-rose-600' : 'text-indigo-700'}">{{ $event->status }}</b><br>
                                <span class="text-slate-500">{{ \Carbon\Carbon::parse($event->event_time)->format('d M Y, H:i') }}</span><br>
                                <p class="mt-1">${"{{ addslashes($event->description) }}"}</p>
                            </div>
                        `);
                    })();
                @endif
            @endforeach

            // Destination Marker
            if (destLat && destLng) {
                const destMarker = L.marker([destLat, destLng]).addTo(map);
                destMarker.bindPopup(`<b>🏁 Tujuan: {{ $shipment->destination->name }}</b><br>{{ $shipment->destination->city }}, {{ $shipment->destination->country }}`);
                points.push([destLat, destLng]);
            }

            // Draw polyline route
            if (points.length >= 2) {
                const polyline = L.polyline(points, {
                    color: '#4f46e5',
                    weight: 3.5,
                    dashArray: '8, 8',
                    opacity: 0.85
                }).addTo(map);
                map.fitBounds(polyline.getBounds(), { padding: [50, 50] });
            }
        });
    </script>
</body>
</html>
