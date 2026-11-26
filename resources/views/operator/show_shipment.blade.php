<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail & Operasional Resi {{ $shipment->shipment_number }} - Operator GoTrace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 380px; z-index: 1; border-radius: 0.75rem; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">
    <!-- Navbar (Tema Teal) -->
    <nav class="bg-teal-700 text-white p-4 shadow-md flex justify-between items-center sticky top-0 z-30">
        <div class="flex items-center gap-3">
            <a href="{{ route('operator.dashboard') }}" class="font-black text-xl tracking-wide flex items-center gap-2">
                GoTrace<span class="text-teal-300">.</span>
            </a>
            <span class="text-xs font-bold bg-teal-800 text-teal-200 px-2.5 py-1 rounded-lg uppercase tracking-wider">Logistics Operator</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('operator.dashboard') }}" class="text-xs font-bold text-teal-100 hover:text-white transition flex items-center gap-1">
                &larr; Kembali ke Dashboard
            </a>
            <form action="{{ route('logout') }}" method="POST" class="inline m-0">
                @csrf
                <button type="submit" class="bg-teal-800 hover:bg-rose-600 px-3.5 py-1.5 rounded-lg text-xs font-bold transition shadow-sm">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow p-6 md:p-8 max-w-7xl mx-auto w-full">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-black text-slate-800 tracking-tight">
                        Resi: <span class="text-teal-700">{{ $shipment->shipment_number }}</span>
                    </h1>
                    @php
                        $statusBadge = match($shipment->status) {
                            'DELIVERED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'IN_TRANSIT' => 'bg-teal-100 text-teal-800 border-teal-200',
                            'EXCEPTION' => 'bg-rose-100 text-rose-800 border-rose-200 animate-pulse',
                            'BOOKED' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'PICKED_UP' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                        };
                    @endphp
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black border uppercase {{ $statusBadge }}">
                        {{ $shipment->status }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Didaftarkan oleh {{ $shipment->creator?->name ?? 'Operator' }} &bull; {{ $shipment->created_at->format('d M Y, H:i') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('operator.dashboard') }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold transition shadow-sm">
                    &larr; Manifes
                </a>
                <a href="{{ route('admin.shipments.print', $shipment->id) }}" target="_blank" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print Label Resi
                </a>
            </div>
        </div>

        <!-- Alert Notifikasi -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-teal-50 border border-teal-200 text-teal-800 text-xs font-bold rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-teal-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                {{ session('error') }}
            </div>
        @endif

        <!-- Layout Grid: Kiri (Peta & Info) | Kanan (Event & Exception) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- KOLOM KIRI (2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Card Peta Rute & Waypoint -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 bg-slate-50/60 flex justify-between items-center">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Visualisasi Rute GIS & Posisi Terkini</h3>
                            <p class="text-[11px] text-slate-500">Peta interaktif Leaflet.js dengan waypoint rute dan koordinat pos logistik.</p>
                        </div>
                        <span class="text-xs font-bold text-teal-700 bg-teal-50 px-2.5 py-1 rounded-lg border border-teal-100">
                            {{ $shipment->trackingEvents->count() }} Checkpoint Terdata
                        </span>
                    </div>
                    <div class="p-4">
                        <div id="map"></div>
                    </div>
                </div>

                <!-- Live Weather Card (Open-Meteo Integration) -->
                @if(isset($weather))
                    <div class="bg-gradient-to-r from-sky-50 to-indigo-50/70 border border-sky-200 rounded-2xl p-5 shadow-sm">
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-white text-sky-600 rounded-xl shadow-sm border border-sky-100">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-sky-600 uppercase tracking-wider block">Telemetri Cuaca Live (Open-Meteo API)</span>
                                    <h4 class="text-base font-bold text-slate-800">{{ $weather['condition'] ?? 'Cuaca Normal' }}</h4>
                                    <p class="text-xs text-slate-500">Dipantau pada koordinat checkpoint terkini kargo.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 text-xs font-bold">
                                <div class="bg-white px-3 py-2 rounded-xl border border-sky-100 text-center">
                                    <span class="text-slate-400 block text-[10px] uppercase">Suhu</span>
                                    <span class="text-slate-800 text-sm">{{ $weather['temperature'] ?? 28 }}&deg;C</span>
                                </div>
                                <div class="bg-white px-3 py-2 rounded-xl border border-sky-100 text-center">
                                    <span class="text-slate-400 block text-[10px] uppercase">Kecepatan Angin</span>
                                    <span class="text-slate-800 text-sm">{{ $weather['wind_speed'] ?? 12 }} km/h</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Shipment Specifications & Customer -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 bg-slate-50/60">
                        <h3 class="text-sm font-bold text-slate-800">Spesifikasi Muatan & Data Kontrak</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                        <div class="space-y-4">
                            <div>
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pelanggan / Pengirim (Shipper)</span>
                                <span class="text-sm font-bold text-slate-800 block">{{ $shipment->customer->name ?? '-' }}</span>
                                <span class="text-slate-500">{{ $shipment->customer->email ?? '' }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Maskapai Logistik (Carrier)</span>
                                <span class="text-sm font-bold text-slate-800 block">{{ $shipment->carrier->name ?? '-' }}</span>
                                <span class="text-slate-500">Kode: {{ $shipment->carrier->code ?? '-' }} &bull; Kontak: {{ $shipment->carrier->contact_info ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jadwal Keberangkatan & Kedatangan</span>
                                <p class="text-slate-700">Depart: <span class="font-bold">{{ $shipment->expected_departure_at ? $shipment->expected_departure_at->format('d M Y H:i') : 'Pending' }}</span></p>
                                <p class="text-teal-700 font-bold mt-0.5">Target ETA: {{ $shipment->expected_arrival_at ? $shipment->expected_arrival_at->format('d M Y H:i') : 'Pending' }}</p>
                            </div>
                        </div>

                        <div class="space-y-4 md:border-l md:border-slate-100 md:pl-6">
                            <div>
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Rute Terminal Asal & Tujuan</span>
                                <div class="text-slate-800 font-semibold space-y-1">
                                    <p class="text-teal-700 font-bold">Asal: {{ $shipment->origin->name ?? '-' }} ({{ $shipment->origin->city ?? '' }})</p>
                                    <p class="text-indigo-700 font-bold">Tujuan: {{ $shipment->destination->name ?? '-' }} ({{ $shipment->destination->city ?? '' }})</p>
                                </div>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Karakteristik Kargo & Ongkos Angkut</span>
                                <p class="text-sm font-semibold text-slate-800 capitalize">{{ $shipment->cargo_type }} &bull; {{ number_format($shipment->weight_kg, 1) }} kg</p>
                                <p class="text-base font-black text-teal-700 mt-1">{{ $shipment->formatted_cost }}</p>
                            </div>
                            
                            <!-- ETA & Delay Analysis -->
                            <div class="pt-3 border-t border-slate-100">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Analisis Keterlambatan Deterministik</span>
                                @if(isset($delay) && $delay['is_delayed'])
                                    <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl">
                                        <div class="flex items-center gap-1.5 font-bold mb-1">
                                            <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                            <span>Terlambat {{ $delay['delay_hours'] }} Jam ({{ $delay['delay_days'] }} Hari)</span>
                                            <span class="px-1.5 py-0.5 rounded bg-rose-200 text-rose-900 text-[10px]">{{ $delay['severity'] }} SEVERITY</span>
                                        </div>
                                        <p class="text-[11px] text-rose-700">{{ $delay['message'] }}</p>
                                    </div>
                                @else
                                    <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl">
                                        <div class="flex items-center gap-1.5 font-bold">
                                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                            <span>Jadwal Operasional Tepat Waktu (On Time)</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cargo Items List -->
                @if($shipment->items && $shipment->items->count() > 0)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-4 border-b border-slate-200 bg-slate-50/60">
                            <h3 class="text-sm font-bold text-slate-800">Daftar Manifes Item Kargo ({{ $shipment->items->count() }})</h3>
                        </div>
                        <div class="p-4 overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="p-2.5">Deskripsi Item</th>
                                        <th class="p-2.5">Tipe Kargo</th>
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

            <!-- KOLOM KANAN: Exception Management, New Tracking Event, Timeline -->
            <div class="space-y-6">

                <!-- 1. EXCEPTION MANAGEMENT SECTION -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                            <h3 class="text-sm font-bold text-slate-800">Manajemen Exception / Kendala</h3>
                        </div>
                        <span class="text-[11px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-100">
                            {{ $shipment->exceptions->whereIn('status', ['OPEN', 'INVESTIGATING'])->count() }} Aktif
                        </span>
                    </div>

                    <!-- Open Exceptions List -->
                    @if($shipment->exceptions && $shipment->exceptions->count() > 0)
                        <div class="space-y-3 mb-5">
                            @foreach($shipment->exceptions as $exc)
                                @php
                                    $isResolved = $exc->status === 'RESOLVED';
                                    $sevColor = match($exc->severity) {
                                        'HIGH' => 'bg-rose-100 text-rose-800 border-rose-200',
                                        'MEDIUM' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <div class="p-3.5 rounded-xl border {{ $isResolved ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/60 border-rose-200' }} text-xs">
                                    <div class="flex justify-between items-start mb-1.5">
                                        <div>
                                            <span class="font-bold text-slate-800 block">{{ str_replace('_', ' ', $exc->type) }}</span>
                                            <span class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($exc->detected_at)->format('d M Y H:i') }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-2 py-0.5 rounded text-[9px] font-black border uppercase {{ $sevColor }}">{{ $exc->severity }}</span>
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold {{ $isResolved ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-200 text-rose-900' }}">
                                                {{ $exc->status }}
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-slate-600 whitespace-pre-line">{{ $exc->description }}</p>

                                    @if(!$isResolved)
                                        <form action="{{ route('exceptions.resolve', $exc->id) }}" method="POST" class="mt-3 pt-2.5 border-t border-rose-100 flex gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="notes" placeholder="Catatan penyelesaian kendala..." required class="text-[11px] px-2.5 py-1.5 border border-slate-300 rounded-lg flex-grow focus:outline-none focus:ring-1 focus:ring-teal-500">
                                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold transition">
                                                Resolve
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Form Laporkan Exception Baru (Jika Shipment belum DELIVERED) -->
                    @if(!in_array(strtoupper($shipment->status), ['DELIVERED', 'CANCELLED']))
                        <div class="pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-bold text-slate-700 mb-2">Laporkan Kendala Baru (Report Exception)</h4>
                            <form action="{{ route('exceptions.store', $shipment) }}" method="POST" class="space-y-3">
                                @csrf
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tipe Kendala</label>
                                        <select name="type" required class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                                            <option value="">-- Pilih Tipe --</option>
                                            @foreach($exceptionTypes ?? ['CUSTOMS_HOLD', 'WEATHER_DELAY', 'PORT_CONGESTION', 'DAMAGED', 'MISROUTED', 'DOCUMENTATION_ISSUE'] as $type)
                                                <option value="{{ $type }}">{{ str_replace('_', ' ', $type) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tingkat Keparahan</label>
                                        <select name="severity" required class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                                            <option value="LOW">LOW (&le; 6 Jam)</option>
                                            <option value="MEDIUM" selected>MEDIUM (6-24 Jam)</option>
                                            <option value="HIGH">HIGH (&gt; 24 Jam)</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Deskripsi Investigasi</label>
                                    <textarea name="description" rows="2" placeholder="Jelaskan alasan kendala dan tindakan yang diambil..." required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"></textarea>
                                </div>
                                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white py-2 rounded-xl text-xs font-bold transition shadow-sm">
                                    ⚠️ Log Exception & Ubah Status
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <!-- 2. LOG TRACKING EVENT & ADVANCE STATUS -->
                @if(!in_array(strtoupper($shipment->status), ['DELIVERED', 'CANCELLED']))
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                        <div class="flex items-center gap-2 pb-3 border-b border-slate-100 mb-4">
                            <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span>
                            <h3 class="text-sm font-bold text-slate-800">Catat Milestone & Transisi Status</h3>
                        </div>

                        <form action="{{ route('tracking.store', $shipment) }}" method="POST" class="space-y-3 text-xs">
                            @csrf
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status Selanjutnya Sesuai Aturan Bisnis <span class="text-rose-500">*</span></label>
                                <select name="status" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none font-bold text-teal-800">
                                    <option value="">-- Pilih Transisi Status Valid --</option>
                                    @foreach($allowedStatuses as $stat)
                                        <option value="{{ $stat }}">&rarr; {{ $stat }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Hub / Lokasi Checkpoint</label>
                                <select name="location_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                                    <option value="">-- Pilih Hub Terdaftar --</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Latitude (Opsional)</label>
                                    <input type="number" step="any" name="latitude" placeholder="cth: 1.2644" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-teal-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Longitude (Opsional)</label>
                                    <input type="number" step="any" name="longitude" placeholder="cth: 103.8403" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-teal-500 focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Catatan Kejadian / Pergerakan</label>
                                <textarea name="description" rows="2" placeholder="cth: Kontainer selesai dimuat dan kapal lepas jangkar..." required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none"></textarea>
                            </div>

                            <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white py-2 rounded-xl text-xs font-bold transition shadow-sm">
                                + Simpan Checkpoint Tracking
                            </button>
                        </form>
                    </div>
                @endif

                <!-- 3. CHRONOLOGICAL TIMELINE -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <h3 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100 mb-4 flex items-center justify-between">
                        <span>Riwayat Timeline Perjalanan</span>
                        <span class="text-xs text-slate-400 font-normal">Kronologis</span>
                    </h3>

                    <div class="relative border-l-2 border-slate-200 ml-3 space-y-6">
                        @forelse($shipment->chronologicalEvents as $ev)
                            @php
                                $isExc = $ev->status === 'EXCEPTION';
                                $isDel = $ev->status === 'DELIVERED';
                                $dotColor = $isExc ? 'bg-rose-600' : ($isDel ? 'bg-emerald-600' : 'bg-teal-600');
                            @endphp
                            <div class="pl-6 relative">
                                <div class="absolute w-3.5 h-3.5 {{ $dotColor }} rounded-full -left-[7.5px] top-1 ring-4 ring-white shadow-sm"></div>
                                <div class="flex justify-between items-start">
                                    <span class="text-[11px] font-bold {{ $isExc ? 'text-rose-700' : 'text-slate-800' }}">
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
                                <span class="text-[10px] text-slate-400 mt-1 block">Oleh: {{ $ev->creator?->name ?? 'System' }}</span>
                            </div>
                        @empty
                            <p class="pl-6 text-xs text-slate-400 italic">Belum ada riwayat tracking tersimpan.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </main>

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
                originMarker.bindPopup(`<b>📍 Origin: {{ $shipment->origin->name }}</b><br>{{ $shipment->origin->city }}, {{ $shipment->origin->country }}`);
                points.push([originLat, originLng]);
            }

            // Tracking Milestones Waypoints
            @foreach($shipment->chronologicalEvents as $event)
                @if($event->latitude && $event->longitude)
                    (function() {
                        const lat = parseFloat("{{ $event->latitude }}");
                        const lng = parseFloat("{{ $event->longitude }}");
                        points.push([lat, lng]);

                        const isException = "{{ $event->status }}" === 'EXCEPTION';
                        const marker = L.circleMarker([lat, lng], {
                            radius: 7,
                            fillColor: isException ? '#e11d48' : '#0d9488',
                            color: '#ffffff',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.9
                        }).addTo(map);

                        marker.bindPopup(`
                            <div class="text-xs">
                                <b class="${isException ? 'text-rose-600' : 'text-teal-700'}">{{ $event->status }}</b><br>
                                <span class="text-slate-500">{{ \Carbon\Carbon::parse($event->event_time)->format('d M Y, H:i') }}</span><br>
                                <p class="mt-1">{{ $event->description }}</p>
                            </div>
                        `);
                    })();
                @endif
            @endforeach

            // Destination Marker
            if (destLat && destLng) {
                const destMarker = L.marker([destLat, destLng]).addTo(map);
                destMarker.bindPopup(`<b>🏁 Destination: {{ $shipment->destination->name }}</b><br>{{ $shipment->destination->city }}, {{ $shipment->destination->country }}`);
                points.push([destLat, destLng]);
            }

            // Draw route polyline
            if (points.length >= 2) {
                const polyline = L.polyline(points, {
                    color: '#0d9488',
                    weight: 3.5,
                    dashArray: '8, 8',
                    opacity: 0.8
                }).addTo(map);
                map.fitBounds(polyline.getBounds(), { padding: [50, 50] });
            }
        });
    </script>
</body>
</html>
