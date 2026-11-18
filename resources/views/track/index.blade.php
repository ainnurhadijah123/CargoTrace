<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Shipment - CargoTrace Global Visibility</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        .leaflet-routing-container, .leaflet-routing-container-hide, .leaflet-right { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 shadow-sm z-50 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('track.index') }}" class="text-2xl font-black text-indigo-900 tracking-tight">
                    CargoTrace<span class="text-indigo-600">.</span>
                </a>
                <span class="hidden sm:inline-block px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Live Telemetry
                </span>
            </div>
            <div class="flex items-center gap-4">
                @auth
                    @php
                        $userRole = is_object(Auth::user()->role) ? Auth::user()->role->value : Auth::user()->role;
                        $dashRoute = match($userRole) {
                            'ADMIN' => route('admin.dashboard'),
                            'OPERATOR' => route('operator.dashboard'),
                            default => route('customer.dashboard'),
                        };
                    @endphp
                    <a href="{{ $dashRoute }}" class="text-xs font-bold text-indigo-700 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl transition">
                        Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-bold text-slate-700 hover:text-indigo-700 bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded-xl transition">
                        Sign In
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="flex-grow flex flex-col">
        
        @if(!isset($shipment))
            <!-- SEARCH HERO SECTION -->
            <div class="flex-grow flex items-center justify-center p-6">
                <div class="w-full max-w-3xl bg-white p-8 md:p-14 rounded-3xl shadow-xl border border-slate-100 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-black text-slate-800 mb-3 tracking-tight">Global Shipment Visibility</h2>
                    <p class="text-slate-500 text-sm md:text-base max-w-lg mx-auto mb-8">Enter your CargoTrace tracking or shipment reference number to view real-time coordinates, timeline milestones, weather conditions, and ETA telemetry.</p>
                    
                    <form action="{{ route('track.search') }}" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-2xl mx-auto">
                        @csrf
                        <div class="relative flex-grow">
                            <input type="text" name="tracking_number" value="{{ old('tracking_number') }}" placeholder="e.g. CT-20261118-SAMPLE" required 
                                class="w-full px-5 py-4 rounded-xl bg-slate-50 border border-slate-300 text-slate-800 placeholder-slate-400 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition font-bold tracking-wider uppercase text-base shadow-inner">
                        </div>
                        <button type="submit" class="py-4 px-8 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition text-base">
                            Track Cargo
                        </button>
                    </form>

                    @if(session('error'))
                        <div class="mt-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm font-semibold max-w-2xl mx-auto">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="mt-10 pt-8 border-t border-slate-100 flex flex-wrap justify-center items-center gap-6 text-xs text-slate-400 font-medium">
                        <span>✓ Deterministic Delay Calculation</span>
                        <span>✓ Live Open-Meteo Weather</span>
                        <span>✓ OpenStreetMap Routing</span>
                    </div>
                </div>
            </div>
        @else
            <!-- TRACKING RESULTS VIEW -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-6">
                
                <!-- Quick Re-Search Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Shipment Number</span>
                            @php
                                $statusBadge = match($shipment->status) {
                                    'DELIVERED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'IN_TRANSIT' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                    'EXCEPTION' => 'bg-rose-100 text-rose-800 border-rose-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black border uppercase {{ $statusBadge }}">{{ $shipment->status }}</span>
                        </div>
                        <h2 class="text-2xl font-black text-slate-800 tracking-tight">{{ $shipment->shipment_number }}</h2>
                    </div>

                    <form action="{{ route('track.search') }}" method="POST" class="flex w-full md:w-auto gap-2">
                        @csrf
                        <input type="text" name="tracking_number" placeholder="Track another ID..." required class="px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-300 focus:border-indigo-600 outline-none text-xs font-bold uppercase w-full md:w-64">
                        <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs transition">Search</button>
                    </form>
                </div>

                <!-- ETA & Delay Status Alert Box -->
                @if(isset($delayStatus))
                    @if($delayStatus['is_delayed'])
                        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-3.5">
                                <div class="p-2.5 bg-amber-100 text-amber-700 rounded-xl mt-0.5">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-black text-amber-900 uppercase tracking-wide">Transit Delay Detected ({{ $delayStatus['severity'] }} Severity)</h4>
                                        <span class="px-2 py-0.5 bg-amber-200 text-amber-900 rounded text-[10px] font-bold">+{{ $delayStatus['delay_hours'] }} Hours</span>
                                    </div>
                                    <p class="text-xs text-amber-800 mt-1">{{ $delayStatus['message'] }}</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-semibold text-amber-700 bg-white/70 px-3 py-1.5 rounded-lg border border-amber-200">
                                Expected: {{ \Carbon\Carbon::parse($delayStatus['expected_arrival'])->format('d M Y, H:i') }}
                            </span>
                        </div>
                    @else
                        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-emerald-100 text-emerald-700 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-emerald-900 uppercase">On Schedule</h4>
                                    <p class="text-xs text-emerald-700">{{ $delayStatus['message'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Horizontal Lifecycle Progress -->
                <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200">
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Shipment Journey Progress</span>
                        <span class="text-xs font-bold text-indigo-700">{{ $shipment->progress_percent }}% Completed</span>
                    </div>

                    <div class="relative w-full max-w-4xl mx-auto my-6">
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-600 rounded-full transition-all duration-700" style="width: {{ $shipment->progress_percent }}%;"></div>
                        </div>

                        <div class="flex justify-between items-center text-[11px] font-bold text-slate-500 mt-3">
                            <span class="{{ $shipment->progress_percent >= 10 ? 'text-indigo-600' : '' }}">Created</span>
                            <span class="{{ $shipment->progress_percent >= 30 ? 'text-indigo-600' : '' }}">Booked</span>
                            <span class="{{ $shipment->progress_percent >= 50 ? 'text-indigo-600' : '' }}">Picked Up</span>
                            <span class="{{ $shipment->progress_percent >= 75 ? 'text-indigo-600' : '' }}">In Transit</span>
                            <span class="{{ $shipment->progress_percent >= 100 ? 'text-emerald-600' : '' }}">Delivered</span>
                        </div>
                    </div>
                </div>

                <!-- Main Grid: Map & Details / Weather -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Map Column (2/3 width) -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="p-4 border-b border-slate-200 bg-slate-50/60 flex justify-between items-center">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800">Geographic Routing & Waypoints</h3>
                                    <p class="text-[11px] text-slate-500">Live coordinates rendered on OpenStreetMap.</p>
                                </div>
                                <span class="text-[10px] bg-indigo-50 text-indigo-700 font-bold px-2 py-1 rounded">Leaflet OSM</span>
                            </div>
                            <div id="map" class="w-full bg-slate-100" style="height: 480px; z-index: 1;"></div>
                        </div>

                        <!-- Chronological Tracking Timeline -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                            <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
                                <div>
                                    <h3 class="text-base font-bold text-slate-800">Tracking Event Timeline</h3>
                                    <p class="text-xs text-slate-500">Immutable audit history of all recorded cargo scan events.</p>
                                </div>
                                <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">
                                    {{ $shipment->chronologicalEvents->count() }} Milestones
                                </span>
                            </div>

                            <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                                @forelse($shipment->chronologicalEvents->reverse() as $event)
                                    @php
                                        $isEventException = $event->status === 'EXCEPTION';
                                        $dotColor = $isEventException ? 'bg-rose-500 ring-rose-200' : ($event->status === 'DELIVERED' ? 'bg-emerald-500 ring-emerald-200' : 'bg-indigo-600 ring-indigo-200');
                                    @endphp
                                    <div class="relative">
                                        <div class="absolute -left-[27px] top-1.5 w-3.5 h-3.5 rounded-full {{ $dotColor }} ring-4 bg-white"></div>
                                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-black uppercase tracking-wider {{ $isEventException ? 'text-rose-600' : 'text-slate-800' }}">
                                                        {{ $event->status }}
                                                    </span>
                                                    @if($event->location)
                                                        <span class="text-xs font-medium text-slate-600">• {{ $event->location->name }} ({{ $event->location->city }})</span>
                                                    @endif
                                                </div>
                                                <span class="text-[11px] text-slate-400 font-medium">
                                                    {{ \Carbon\Carbon::parse($event->event_time)->format('d M Y, H:i') }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-600 leading-relaxed">{{ $event->description }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 italic">No tracking events recorded yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Side Cards: Route, Cargo & Live Weather (1/3 width) -->
                    <div class="lg:col-span-1 space-y-6">
                        
                        <!-- Live Weather Card (External API) -->
                        @if(isset($weather) && $weather['success'])
                            <div class="bg-gradient-to-br from-indigo-900 to-slate-900 text-white p-6 rounded-2xl shadow-md relative overflow-hidden">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-300">Live Weather Telemetry</span>
                                        <h4 class="text-base font-bold mt-0.5">Current Checkpoint</h4>
                                    </div>
                                    <span class="text-3xl">{{ $weather['icon'] }}</span>
                                </div>
                                <div class="flex items-baseline gap-2 mb-3">
                                    <span class="text-3xl font-black">{{ $weather['temperature'] }}°C</span>
                                    <span class="text-xs text-indigo-200 font-medium">{{ $weather['condition'] }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-[11px] text-indigo-200 pt-3 border-t border-indigo-800/60">
                                    <div>Windspeed: <b class="text-white">{{ $weather['windspeed'] }} km/h</b></div>
                                    <div class="text-right">Source: <b class="text-white">{{ $weather['source'] }}</b></div>
                                </div>
                            </div>
                        @endif

                        <!-- Route Hubs Card -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Route Schedule</h4>
                            <div>
                                <p class="text-[11px] text-slate-400 font-bold uppercase">Origin Port / Hub</p>
                                <p class="text-sm font-bold text-slate-800">{{ $shipment->origin?->name ?? 'N/A' }}</p>
                                <p class="text-xs text-slate-500">{{ $shipment->origin?->city }}, {{ $shipment->origin?->country }}</p>
                            </div>
                            <div class="border-t border-slate-100 pt-3">
                                <p class="text-[11px] text-slate-400 font-bold uppercase">Destination Port / Hub</p>
                                <p class="text-sm font-bold text-slate-800">{{ $shipment->destination?->name ?? 'N/A' }}</p>
                                <p class="text-xs text-slate-500">{{ $shipment->destination?->city }}, {{ $shipment->destination?->country }}</p>
                            </div>
                        </div>

                        <!-- Cargo Details Card -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-3">
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Cargo & Carrier Specifications</h4>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500">Carrier Partner:</span>
                                <span class="font-bold text-slate-800">{{ $shipment->carrier?->name }} ({{ $shipment->carrier?->code }})</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500">Cargo Type:</span>
                                <span class="font-bold text-slate-800">{{ $shipment->cargo_type ?? 'General Freight' }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500">Gross Weight:</span>
                                <span class="font-bold text-slate-800">{{ number_format($shipment->weight_kg, 2) }} kg</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500">Expected Arrival:</span>
                                <span class="font-bold text-indigo-700">
                                    {{ $shipment->expected_arrival_at ? $shipment->expected_arrival_at->format('d M Y, H:i') : 'Pending' }}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Leaflet JS -->
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var originLat = {{ $shipment->origin?->latitude ?? 1.2644 }};
                    var originLng = {{ $shipment->origin?->longitude ?? 103.8403 }};
                    var destLat = {{ $shipment->destination?->latitude ?? 51.9054 }};
                    var destLng = {{ $shipment->destination?->longitude ?? 4.4666 }};

                    var map = L.map('map').setView([originLat, originLng], 4);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 18,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors | CargoTrace'
                    }).addTo(map);

                    var waypoints = [];
                    waypoints.push([originLat, originLng]);

                    // Add Origin Marker
                    var originIcon = L.divIcon({
                        className: 'origin-marker',
                        html: '<div style="background:#4f46e5; color:#fff; width:22px; height:22px; border-radius:50%; border:3px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.3); display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:bold;">A</div>',
                        iconSize: [22, 22],
                        iconAnchor: [11, 11]
                    });
                    L.marker([originLat, originLng], { icon: originIcon }).addTo(map)
                        .bindPopup('<b>Origin:</b> {{ $shipment->origin?->name }} ({{ $shipment->origin?->city }})');

                    // Intermediate Tracking Checkpoints
                    @foreach($shipment->chronologicalEvents as $ev)
                        @if($ev->latitude && $ev->longitude)
                            waypoints.push([{{ $ev->latitude }}, {{ $ev->longitude }}]);
                            var isExc = '{{ $ev->status }}' === 'EXCEPTION';
                            var cpColor = isExc ? '#e11d48' : '#6366f1';
                            var cpIcon = L.divIcon({
                                className: 'cp-marker',
                                html: '<div style="background:'+cpColor+'; width:14px; height:14px; border-radius:50%; border:2px solid #fff; box-shadow:0 2px 4px rgba(0,0,0,0.3);"></div>',
                                iconSize: [14, 14],
                                iconAnchor: [7, 7]
                            });
                            L.marker([{{ $ev->latitude }}, {{ $ev->longitude }}], { icon: cpIcon }).addTo(map)
                                .bindPopup('<b>{{ $ev->status }}:</b> {{ addslashes($ev->description) }}<br><small>{{ \Carbon\Carbon::parse($ev->event_time)->format("d M Y H:i") }}</small>');
                        @endif
                    @endforeach

                    // Add Destination Marker
                    waypoints.push([destLat, destLng]);
                    var destIcon = L.divIcon({
                        className: 'dest-marker',
                        html: '<div style="background:#10b981; color:#fff; width:22px; height:22px; border-radius:50%; border:3px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.3); display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:bold;">B</div>',
                        iconSize: [22, 22],
                        iconAnchor: [11, 11]
                    });
                    L.marker([destLat, destLng], { icon: destIcon }).addTo(map)
                        .bindPopup('<b>Destination:</b> {{ $shipment->destination?->name }} ({{ $shipment->destination?->city }})');

                    // Draw Route Polyline
                    var polyline = L.polyline(waypoints, {
                        color: '#4f46e5',
                        weight: 4,
                        opacity: 0.85,
                        dashArray: '8, 8'
                    }).addTo(map);

                    map.fitBounds(polyline.getBounds(), { padding: [50, 50] });
                });
            </script>
        @endif
    </main>

    <!-- Footer with attribution -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500">
            <p>CargoTrace Global Shipment Visibility & Tracking Platform &bull; Web Programming 2 UAS</p>
            <p class="mt-1 text-slate-400">Map data &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" class="underline">OpenStreetMap</a> contributors &bull; Weather powered by <a href="https://open-meteo.com/" target="_blank" class="underline">Open-Meteo</a></p>
        </div>
    </footer>
</body>
</html>
