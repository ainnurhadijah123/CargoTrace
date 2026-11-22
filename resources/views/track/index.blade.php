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

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .leaflet-routing-container, .leaflet-routing-container-hide, .leaflet-right { display: none !important; }
        
        /* Custom scrollbar */
        .hud-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .hud-scroll::-webkit-scrollbar-track { background: #f0f9ff; }
        .hud-scroll::-webkit-scrollbar-thumb { background: #7dd3fc; border-radius: 4px; }
        .hud-scroll::-webkit-scrollbar-thumb:hover { background: #38bdf8; }

        @keyframes float-cloud {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        .animate-float {
            animation: float-cloud 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-sky-50/40 font-sans text-slate-800 min-h-screen flex flex-col selection:bg-sky-400 selection:text-white">

    <!-- Navbar: Sky Blue with Cloud White typography -->
    <nav class="bg-[#177be5] border-b border-white/20 sticky top-0 z-50 transition-colors shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('track.index') }}" class="text-2xl font-black text-white tracking-tight flex items-center gap-2 group">
                    <span class="p-1.5 rounded-xl bg-white text-[#177be5] shadow-md group-hover:scale-105 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </span>
                    <span>CargoTrace<span class="text-amber-300">.</span></span>
                </a>
                <span class="hidden md:inline-flex items-center gap-1.5 px-3 py-0.5 text-[11px] font-bold rounded-full bg-white/20 text-white border border-white/30 backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-300 animate-ping"></span>
                    Live 3D Telemetry
                </span>
            </div>
            
            <div class="flex items-center gap-3 sm:gap-6">
                <a href="#active-routes" class="hidden sm:inline-block text-xs font-bold text-white/90 hover:text-white transition">
                    Trade Corridors
                </a>
                <a href="#platform-specs" class="hidden sm:inline-block text-xs font-bold text-white/90 hover:text-white transition">
                    Specs & Architecture
                </a>

                @auth
                    @php
                        $userRole = is_object(Auth::user()->role) ? Auth::user()->role->value : Auth::user()->role;
                        $dashRoute = match($userRole) {
                            'ADMIN' => route('admin.dashboard'),
                            'OPERATOR' => route('operator.dashboard'),
                            default => route('customer.dashboard'),
                        };
                    @endphp
                    <div class="flex items-center gap-2">
                        <span class="hidden lg:inline-block px-2.5 py-0.5 text-[10px] font-mono font-bold rounded-full bg-white/20 text-white border border-white/30">
                            {{ $userRole }}
                        </span>
                        <a href="{{ $dashRoute }}" class="text-xs font-bold text-[#177be5] bg-white hover:bg-sky-50 shadow-md px-4 py-2 rounded-xl transition flex items-center gap-1.5">
                            <span>Dashboard</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-bold text-[#177be5] bg-white hover:bg-sky-50 shadow-md px-5 py-2 rounded-xl transition">
                        Sign In
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="flex-grow flex flex-col">
        
        @if(!isset($shipment))
            <!-- ======================================================== -->
            <!-- SKY BLUE & CLOUD WHITE 3D GLOBE HERO EXPERIENCE          -->
            <!-- ======================================================== -->
            <section class="relative min-h-[90vh] lg:min-h-[92vh] flex flex-col justify-center overflow-hidden bg-gradient-to-b from-[#1570df] via-[#2189f7] to-[#4fa7f9] text-white border-b border-sky-300/30">
                
                <!-- Ambient Sunlight & Soft Cloud Layers -->
                <div class="absolute right-10 top-1/4 w-[600px] h-[600px] bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute left-10 bottom-10 w-96 h-96 bg-sky-200/20 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Realistic Fluffy White Cloud Wave at the base of Hero -->
                <div class="absolute bottom-0 inset-x-0 pointer-events-none z-10">
                    <svg class="w-full h-20 sm:h-28 text-sky-50/90 fill-current preserve-3d" viewBox="0 0 1440 120" preserveAspectRatio="none">
                        <path d="M0,32L48,42.7C96,53,192,75,288,80C384,85,480,75,576,64C672,53,768,43,864,48C960,53,1056,75,1152,80C1248,85,1344,75,1392,69.3L1440,64L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
                    </svg>
                </div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center flex-grow">
                    
                    <!-- Left Hero Content & Search Box (5 cols) -->
                    <div class="lg:col-span-5 flex flex-col justify-center space-y-6">
                        
                        <!-- Telemetry Status Tag (Cloud White Pill) -->
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/20 border border-white/30 text-xs font-semibold text-white w-fit backdrop-blur-md shadow-sm">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-300 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-300"></span>
                            </span>
                            <span class="font-mono text-[11px] uppercase tracking-wider text-white">REAL-TIME GLOBAL TELEMETRY</span>
                            <span class="text-white/60">•</span>
                            <span class="text-[11px] text-white/90">{{ count($globeRoutes) }} Trade Lanes</span>
                        </div>

                        <!-- Main Bold Headline (Bold style inspired by PLAY STRONG) -->
                        <div>
                            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight uppercase leading-[1.05] drop-shadow-sm">
                                GLOBAL<br>VISIBILITY.
                            </h1>
                            <p class="mt-4 text-white/90 text-sm sm:text-base font-medium leading-relaxed max-w-lg">
                                Real-time multimodal cargo visibility across intercontinental air, ocean, and ground freight. Track live coordinates, weather checkpoints, and automated ETA delay analytics.
                            </p>
                        </div>

                        <!-- Tracking Search Box (Clean Cloud White Card) -->
                        <div class="bg-white/95 backdrop-blur-md p-3 rounded-2xl shadow-2xl border border-white/80 text-slate-800">
                            <form action="{{ route('track.search') }}" method="POST" class="flex flex-col sm:flex-row gap-2">
                                @csrf
                                <div class="relative flex-grow">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-sky-500">
                                        <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                    <input type="text" name="tracking_number" id="tracking_input" value="{{ old('tracking_number') }}" 
                                        placeholder="ENTER RESI: CT-20261118-SEAEUR" required 
                                        class="w-full pl-11 pr-4 py-3.5 rounded-xl bg-sky-50/70 border border-sky-200 text-slate-900 placeholder-sky-400 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-200 outline-none transition font-mono font-bold tracking-wider uppercase text-sm">
                                </div>
                                <button type="submit" class="py-3.5 px-6 bg-gradient-to-r from-[#177be5] to-sky-500 hover:from-[#1366c0] hover:to-sky-600 text-white font-bold rounded-xl shadow-lg shadow-sky-500/30 transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
                                    <span>Track Cargo</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </button>
                            </form>

                            @if(session('error'))
                                <div class="mt-3 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold flex items-center gap-2">
                                    <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span>{{ session('error') }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Quick-Click Sample Shipments (Cloud White Pills) -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs text-white/90 font-bold">
                                <span class="uppercase tracking-wider">Quick Track Scenarios:</span>
                                <span class="text-amber-200 text-[11px]">Click to trace</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="quickFillTrack('CT-20261118-SEAEUR')" 
                                    class="group text-left px-3 py-1.5 rounded-xl bg-white/90 hover:bg-white text-slate-800 border border-white/90 shadow-md backdrop-blur transition hover:scale-[1.02]">
                                    <span class="block text-[11px] font-mono font-bold text-[#177be5]">CT-20261118-SEAEUR</span>
                                    <span class="block text-[10px] text-slate-500 font-medium">Singapore ➔ Rotterdam (Transit)</span>
                                </button>
                                
                                <button type="button" onclick="quickFillTrack('CT-20261118-ASIANAM')" 
                                    class="group text-left px-3 py-1.5 rounded-xl bg-white/90 hover:bg-white text-slate-800 border border-white/90 shadow-md backdrop-blur transition hover:scale-[1.02]">
                                    <span class="block text-[11px] font-mono font-bold text-rose-600">CT-20261118-ASIANAM</span>
                                    <span class="block text-[10px] text-slate-500 font-medium">Shanghai ➔ LA (Delay Alert)</span>
                                </button>
                                
                                <button type="button" onclick="quickFillTrack('CT-20261118-SEAAUS')" 
                                    class="group text-left px-3 py-1.5 rounded-xl bg-white/90 hover:bg-white text-slate-800 border border-white/90 shadow-md backdrop-blur transition hover:scale-[1.02]">
                                    <span class="block text-[11px] font-mono font-bold text-emerald-600">CT-20261118-SEAAUS</span>
                                    <span class="block text-[10px] text-slate-500 font-medium">Jakarta ➔ Sydney (Active)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Live Telemetry KPI Counters (Cloud White Frosted Cards) -->
                        <div class="grid grid-cols-4 gap-2 pt-2">
                            <div class="p-2.5 rounded-2xl bg-white/90 backdrop-blur-md border border-white/80 text-center shadow-lg">
                                <div class="text-xl font-black font-mono text-slate-800">{{ $stats['total'] }}</div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">Total</div>
                            </div>
                            <div class="p-2.5 rounded-2xl bg-white/90 backdrop-blur-md border border-white/80 text-center shadow-lg">
                                <div class="text-xl font-black font-mono text-[#177be5]">{{ $stats['in_transit'] }}</div>
                                <div class="text-[10px] font-bold text-sky-700 uppercase tracking-tight">Transit</div>
                            </div>
                            <div class="p-2.5 rounded-2xl bg-white/90 backdrop-blur-md border border-white/80 text-center shadow-lg">
                                <div class="text-xl font-black font-mono text-rose-500">{{ $stats['exceptions'] }}</div>
                                <div class="text-[10px] font-bold text-rose-600 uppercase tracking-tight">Delays</div>
                            </div>
                            <div class="p-2.5 rounded-2xl bg-white/90 backdrop-blur-md border border-white/80 text-center shadow-lg">
                                <div class="text-xl font-black font-mono text-emerald-600">{{ $stats['delivered'] }}</div>
                                <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-tight">Arrived</div>
                            </div>
                        </div>

                    </div>

                    <!-- Right 3D Interactive WebGL Globe Stage (7 cols) -->
                    <div class="lg:col-span-7 relative h-[520px] sm:h-[620px] lg:h-[720px] w-full flex items-center justify-center">
                        
                        <!-- 3D Canvas Container -->
                        <div id="globe-container" class="w-full h-full relative cursor-grab active:cursor-grabbing select-none">
                            <canvas id="globe-canvas" class="w-full h-full block"></canvas>
                        </div>

                        <!-- 3D Globe HUD Overlay: Top Right Controls (Cloud White Glass) -->
                        <div class="absolute top-4 right-4 z-20 flex flex-col gap-2">
                            <button id="btn-toggle-rotate" type="button" title="Toggle Auto-Rotation"
                                class="px-3.5 py-1.5 bg-white/90 hover:bg-white text-slate-800 border border-white/80 rounded-xl text-xs font-mono font-bold backdrop-blur shadow-lg transition flex items-center gap-1.5">
                                <span id="rotate-dot" class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span id="rotate-text">Rotate: ON</span>
                            </button>
                            <div class="flex gap-1 justify-end">
                                <button id="btn-zoom-in" type="button" title="Zoom In"
                                    class="w-8 h-8 bg-white/90 hover:bg-white text-slate-800 border border-white/80 rounded-xl text-base font-bold backdrop-blur shadow-md flex items-center justify-center transition">
                                    +
                                </button>
                                <button id="btn-zoom-out" type="button" title="Zoom Out"
                                    class="w-8 h-8 bg-white/90 hover:bg-white text-slate-800 border border-white/80 rounded-xl text-base font-bold backdrop-blur shadow-md flex items-center justify-center transition">
                                    −
                                </button>
                                <button id="btn-reset-view" type="button" title="Reset View"
                                    class="px-2.5 h-8 bg-white/90 hover:bg-white text-slate-800 border border-white/80 rounded-xl text-xs font-mono font-bold backdrop-blur shadow-md flex items-center justify-center transition">
                                    Reset
                                </button>
                            </div>
                        </div>

                        <!-- Arc Status Legend: Bottom Left of 3D Stage (Cloud White Glass) -->
                        <div class="absolute bottom-6 left-4 z-20 bg-white/95 backdrop-blur-md border border-white/90 px-4 py-2.5 rounded-2xl shadow-xl flex items-center gap-4 text-xs font-mono font-bold text-slate-700">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-1.5 rounded-full bg-sky-500 shadow-sm"></span>
                                <span>In Transit</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-1.5 rounded-full bg-rose-500 shadow-sm"></span>
                                <span>Exception</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-1.5 rounded-full bg-emerald-500 shadow-sm"></span>
                                <span>Delivered</span>
                            </div>
                        </div>

                        <!-- Active Route Mini Drawer: Bottom Right (Cloud White Glass) -->
                        <div class="absolute bottom-6 right-4 z-20 max-w-xs hidden sm:block">
                            <div class="bg-white/95 backdrop-blur-md border border-white/90 rounded-2xl p-3 shadow-2xl text-slate-800">
                                <div class="flex items-center justify-between pb-2 mb-2 border-b border-sky-100 text-[10px] font-mono font-bold text-sky-700 uppercase tracking-wider">
                                    <span>Active Global Hubs</span>
                                    <span class="text-[#177be5] font-black">10 Ports</span>
                                </div>
                                <div class="space-y-1.5 max-h-28 overflow-y-auto hud-scroll pr-1">
                                    @foreach(collect($globeRoutes)->take(4) as $rt)
                                        <div onclick="focusRoute('{{ $rt['resi'] }}')" class="flex items-center justify-between text-[11px] p-1.5 rounded-xl hover:bg-sky-50 cursor-pointer transition">
                                            <div class="flex items-center gap-1.5">
                                                @php
                                                    $pillColor = match($rt['status']) {
                                                        'DELIVERED' => 'bg-emerald-500',
                                                        'EXCEPTION' => 'bg-rose-500',
                                                        default => 'bg-sky-500',
                                                    };
                                                @endphp
                                                <span class="w-1.5 h-1.5 rounded-full {{ $pillColor }}"></span>
                                                <span class="font-mono font-bold text-slate-800">{{ $rt['origin']['code'] }} ➔ {{ $rt['destination']['code'] }}</span>
                                            </div>
                                            <span class="text-[10px] text-[#177be5] font-mono font-bold hover:underline">Focus</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </section>

            <!-- ======================================================== -->
            <!-- ACTIVE TRADE CORRIDORS GRID (Fresh Cloud White Cards)    -->
            <!-- ======================================================== -->
            <section id="active-routes" class="py-16 bg-sky-50/70 border-b border-sky-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
                        <div>
                            <span class="text-xs font-mono font-bold uppercase tracking-widest text-[#177be5]">Intercontinental Corridors</span>
                            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 mt-1">Monitored Global Shipments</h2>
                            <p class="text-sm text-slate-600 mt-1 max-w-xl">Live shipment pipelines configured in accordance with the Web Programming 2 final project specifications.</p>
                        </div>
                        <div class="mt-4 md:mt-0 flex items-center gap-2 text-xs font-mono font-bold text-sky-700 bg-white px-3 py-1.5 rounded-xl border border-sky-100 shadow-sm">
                            <span>Showing {{ count($allShipments) }} Active Pipelines</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($allShipments as $s)
                            @php
                                $statusBadgeClass = match($s->status) {
                                    'DELIVERED' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'EXCEPTION' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'IN_TRANSIT' => 'bg-sky-50 text-[#177be5] border-sky-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <div class="bg-white rounded-2xl border border-sky-100 p-6 flex flex-col justify-between hover:border-sky-300 hover:shadow-xl transition duration-300 shadow-sm group">
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="font-mono text-xs font-bold text-slate-700 group-hover:text-[#177be5] transition">{{ $s->shipment_number }}</span>
                                        <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold rounded-full border uppercase {{ $statusBadgeClass }}">
                                            {{ $s->status }}
                                        </span>
                                    </div>

                                    <!-- Hub to Hub Direction -->
                                    <div class="my-4 p-3.5 bg-sky-50/70 rounded-xl border border-sky-100">
                                        <div class="flex items-center justify-between text-xs">
                                            <div class="text-left">
                                                <div class="text-[10px] text-slate-400 uppercase font-mono font-semibold">Origin</div>
                                                <div class="font-black text-slate-900">{{ $s->origin?->city ?? 'Origin' }}</div>
                                                <div class="text-[10px] text-sky-700 font-mono font-bold">{{ $s->origin?->code }}</div>
                                            </div>
                                            <div class="px-3 flex flex-col items-center">
                                                <svg class="w-5 h-5 text-[#177be5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-[10px] text-slate-400 uppercase font-mono font-semibold">Destination</div>
                                                <div class="font-black text-slate-900">{{ $s->destination?->city ?? 'Destination' }}</div>
                                                <div class="text-[10px] text-sky-700 font-mono font-bold">{{ $s->destination?->code }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5 text-xs text-slate-600">
                                        <div class="flex justify-between">
                                            <span>Carrier:</span>
                                            <span class="font-bold text-slate-800">{{ $s->carrier?->name }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Cargo:</span>
                                            <span class="font-medium text-slate-700">{{ $s->cargo_type }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Gross Weight:</span>
                                            <span class="font-mono font-bold text-slate-800">{{ number_format($s->weight_kg, 0) }} kg</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 pt-4 border-t border-sky-100 flex items-center justify-between">
                                    <form action="{{ route('track.search') }}" method="POST" class="w-full">
                                        @csrf
                                        <input type="hidden" name="tracking_number" value="{{ $s->shipment_number }}">
                                        <button type="submit" class="w-full py-2.5 px-4 bg-sky-50 hover:bg-[#177be5] text-[#177be5] hover:text-white border border-sky-200 hover:border-[#177be5] font-bold rounded-xl text-xs transition duration-200 flex items-center justify-center gap-2 shadow-sm">
                                            <span>Track Milestone Details</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </section>

            <!-- ======================================================== -->
            <!-- SPECS & SYSTEM ARCHITECTURE                              -->
            <!-- ======================================================== -->
            <section id="platform-specs" class="py-16 bg-white text-slate-800 border-b border-sky-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    <div class="text-center max-w-2xl mx-auto mb-12">
                        <span class="text-xs font-mono font-bold uppercase tracking-widest text-[#177be5]">Architecture Overview</span>
                        <h2 class="text-3xl font-black text-slate-900 mt-1">Engineered for Global Logistics</h2>
                        <p class="text-sm text-slate-600 mt-2">Comprehensive stack built with Laravel 12, SQLite, Three.js WebGL, Leaflet OSM, Open-Meteo REST API, and RBAC security.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <div class="p-6 rounded-2xl bg-sky-50/60 border border-sky-100 space-y-3 shadow-sm hover:border-sky-300 transition">
                            <div class="w-12 h-12 rounded-2xl bg-[#177be5] text-white flex items-center justify-center shadow-md shadow-sky-500/25">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Deterministic Delay Engine</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Automatically categorizes transit deviations into LOW (+6h), MEDIUM (+18h), and HIGH (+48h) severity, dynamically updating expected arrival times.
                            </p>
                        </div>

                        <div class="p-6 rounded-2xl bg-sky-50/60 border border-sky-100 space-y-3 shadow-sm hover:border-sky-300 transition">
                            <div class="w-12 h-12 rounded-2xl bg-sky-500 text-white flex items-center justify-center shadow-md shadow-sky-500/25">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Live Open-Meteo Satellite Data</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Real-time atmospheric conditions, wind speed, and meteorological telemetry queried at active ocean coordinates without requiring API keys.
                            </p>
                        </div>

                        <div class="p-6 rounded-2xl bg-sky-50/60 border border-sky-100 space-y-3 shadow-sm hover:border-sky-300 transition">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-md shadow-emerald-500/25">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Role-Based Access Control</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Strict role separation across Admin, Logistics Operator, and Customer portals with audit trail logging and printable airway bills (Resi).
                            </p>
                        </div>

                    </div>

                </div>
            </section>

        @else
            <!-- ======================================================== -->
            <!-- DETAILED SHIPMENT TRACKING RESULTS VIEW                  -->
            <!-- ======================================================== -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-6">
                
                <!-- Quick Re-Search Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-sky-100">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest">Shipment Resi</span>
                            @php
                                $statusBadge = match($shipment->status) {
                                    'DELIVERED' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'IN_TRANSIT' => 'bg-sky-50 text-[#177be5] border-sky-200',
                                    'EXCEPTION' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-black border uppercase {{ $statusBadge }}">{{ $shipment->status }}</span>
                        </div>
                        <h2 class="text-2xl font-black font-mono text-slate-900 tracking-tight">{{ $shipment->shipment_number }}</h2>
                    </div>

                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <a href="{{ route('track.index') }}" class="px-4 py-2.5 bg-sky-50 hover:bg-sky-100 text-[#177be5] border border-sky-200 font-bold rounded-xl text-xs transition flex items-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4 text-[#177be5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            <span>Back to 3D Globe</span>
                        </a>

                        <form action="{{ route('track.search') }}" method="POST" class="flex w-full md:w-auto gap-2">
                            @csrf
                            <input type="text" name="tracking_number" placeholder="Track another ID..." required class="px-4 py-2.5 rounded-xl bg-sky-50/50 border border-sky-200 focus:bg-white focus:border-[#177be5] outline-none text-xs font-mono font-bold uppercase text-slate-800 w-full md:w-60">
                            <button type="submit" class="px-5 py-2.5 bg-[#177be5] hover:bg-[#1366c0] text-white font-bold rounded-xl text-xs transition shadow-md shadow-sky-500/20">Search</button>
                        </form>
                    </div>
                </div>

                <!-- ETA & Delay Status Alert Box -->
                @if(isset($delayStatus))
                    @if($delayStatus['is_delayed'])
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                            <div class="flex items-start gap-3.5">
                                <div class="p-2.5 bg-amber-100 text-amber-700 rounded-xl mt-0.5 border border-amber-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-black text-amber-900 uppercase tracking-wide">Transit Delay Detected ({{ $delayStatus['severity'] }} Severity)</h4>
                                        <span class="px-2 py-0.5 bg-amber-200 text-amber-900 rounded text-[10px] font-mono font-bold">+{{ $delayStatus['delay_hours'] }} Hours</span>
                                    </div>
                                    <p class="text-xs text-amber-800 mt-1">{{ $delayStatus['message'] }}</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono font-semibold text-amber-800 bg-white px-3 py-1.5 rounded-lg border border-amber-200 shadow-sm">
                                Expected: {{ \Carbon\Carbon::parse($delayStatus['expected_arrival'])->format('d M Y, H:i') }}
                            </span>
                        </div>
                    @else
                        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-emerald-100 text-emerald-700 rounded-lg border border-emerald-200">
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
                <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-sky-100">
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest">Shipment Journey Progress</span>
                        <span class="text-xs font-mono font-bold text-[#177be5]">{{ $shipment->progress_percent }}% Completed</span>
                    </div>

                    <div class="relative w-full max-w-4xl mx-auto my-6">
                        <div class="w-full bg-sky-50 h-2.5 rounded-full overflow-hidden border border-sky-100">
                            <div class="h-full bg-gradient-to-r from-sky-400 to-[#177be5] rounded-full transition-all duration-700" style="width: {{ $shipment->progress_percent }}%;"></div>
                        </div>

                        <div class="flex justify-between items-center text-[11px] font-mono font-bold text-slate-400 mt-3">
                            <span class="{{ $shipment->progress_percent >= 10 ? 'text-[#177be5]' : '' }}">Created</span>
                            <span class="{{ $shipment->progress_percent >= 30 ? 'text-[#177be5]' : '' }}">Booked</span>
                            <span class="{{ $shipment->progress_percent >= 50 ? 'text-[#177be5]' : '' }}">Picked Up</span>
                            <span class="{{ $shipment->progress_percent >= 75 ? 'text-[#177be5]' : '' }}">In Transit</span>
                            <span class="{{ $shipment->progress_percent >= 100 ? 'text-emerald-600' : '' }}">Delivered</span>
                        </div>
                    </div>
                </div>

                <!-- Main Grid: Leaflet Map & Timeline / Weather -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Map Column (2/3 width) -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
                            <div class="p-4 border-b border-sky-100 bg-sky-50/50 flex justify-between items-center">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">Geographic Routing & Waypoints</h3>
                                    <p class="text-[11px] text-slate-500">Live coordinates plotted on OpenStreetMap.</p>
                                </div>
                                <span class="text-[10px] font-mono bg-sky-100 text-[#177be5] border border-sky-200 font-bold px-2 py-1 rounded">Leaflet OSM</span>
                            </div>
                            <div id="map" class="w-full bg-slate-100" style="height: 480px; z-index: 1;"></div>
                        </div>

                        <!-- Chronological Tracking Timeline -->
                        <div class="bg-white rounded-2xl shadow-sm border border-sky-100 p-6">
                            <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
                                <div>
                                    <h3 class="text-base font-bold text-slate-900">Tracking Event Timeline</h3>
                                    <p class="text-xs text-slate-500">Immutable audit history of all recorded cargo scan events.</p>
                                </div>
                                <span class="text-xs font-mono font-semibold px-2.5 py-1 bg-sky-50 text-[#177be5] rounded-lg border border-sky-100">
                                    {{ $shipment->chronologicalEvents->count() }} Milestones
                                </span>
                            </div>

                            <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-sky-100">
                                @forelse($shipment->chronologicalEvents->reverse() as $event)
                                    @php
                                        $isEventException = $event->status === 'EXCEPTION';
                                        $dotColor = $isEventException ? 'bg-rose-500 ring-rose-100' : ($event->status === 'DELIVERED' ? 'bg-emerald-500 ring-emerald-100' : 'bg-[#177be5] ring-sky-100');
                                    @endphp
                                    <div class="relative">
                                        <div class="absolute -left-[27px] top-1.5 w-3.5 h-3.5 rounded-full {{ $dotColor }} ring-4 bg-white"></div>
                                        <div class="bg-sky-50/40 p-4 rounded-xl border border-sky-100">
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-mono font-black uppercase tracking-wider {{ $isEventException ? 'text-rose-600' : 'text-[#177be5]' }}">
                                                        {{ $event->status }}
                                                    </span>
                                                    @if($event->location)
                                                        <span class="text-xs font-medium text-slate-700">• {{ $event->location->name }} ({{ $event->location->city }})</span>
                                                    @endif
                                                </div>
                                                <span class="text-[11px] font-mono text-slate-400">
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
                        
                        <!-- Live Weather Card (Open-Meteo in Sky Blue Gradient) -->
                        @if(isset($weather) && $weather['success'])
                            <div class="bg-gradient-to-br from-[#177be5] to-sky-500 text-white p-6 rounded-2xl shadow-lg border border-sky-400/40 relative overflow-hidden">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-sky-100">Live Weather Telemetry</span>
                                        <h4 class="text-base font-bold mt-0.5">Current Checkpoint</h4>
                                    </div>
                                    <span class="text-3xl">{{ $weather['icon'] }}</span>
                                </div>
                                <div class="flex items-baseline gap-2 mb-3">
                                    <span class="text-3xl font-black font-mono">{{ $weather['temperature'] }}°C</span>
                                    <span class="text-xs text-sky-100 font-medium">{{ $weather['condition'] }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-[11px] font-mono text-sky-100 pt-3 border-t border-white/20">
                                    <div>Wind: <b class="text-white">{{ $weather['windspeed'] }} km/h</b></div>
                                    <div class="text-right">Source: <b class="text-white">{{ $weather['source'] }}</b></div>
                                </div>
                            </div>
                        @endif

                        <!-- Route Hubs Card -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-sky-100 space-y-4">
                            <h4 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest">Route Schedule</h4>
                            <div>
                                <p class="text-[10px] text-slate-400 font-mono font-bold uppercase">Origin Port / Hub</p>
                                <p class="text-sm font-bold text-slate-900">{{ $shipment->origin?->name ?? 'N/A' }}</p>
                                <p class="text-xs text-slate-500">{{ $shipment->origin?->city }}, {{ $shipment->origin?->country }}</p>
                            </div>
                            <div class="border-t border-slate-100 pt-3">
                                <p class="text-[10px] text-slate-400 font-mono font-bold uppercase">Destination Port / Hub</p>
                                <p class="text-sm font-bold text-slate-900">{{ $shipment->destination?->name ?? 'N/A' }}</p>
                                <p class="text-xs text-slate-500">{{ $shipment->destination?->city }}, {{ $shipment->destination?->country }}</p>
                            </div>
                        </div>

                        <!-- Cargo Details Card -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-sky-100 space-y-3">
                            <h4 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest">Cargo Specifications</h4>
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
                                <span class="font-mono font-bold text-slate-800">{{ number_format($shipment->weight_kg, 2) }} kg</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500">Expected Arrival:</span>
                                <span class="font-mono font-bold text-[#177be5]">
                                    {{ $shipment->expected_arrival_at ? $shipment->expected_arrival_at->format('d M Y, H:i') : 'Pending' }}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Leaflet JS for Tracking Results -->
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
                        html: '<div style="background:#177be5; color:#fff; width:22px; height:22px; border-radius:50%; border:3px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.3); display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:bold;">A</div>',
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
                            var cpColor = isExc ? '#f43f5e' : '#177be5';
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
                        color: '#177be5',
                        weight: 4,
                        opacity: 0.9,
                        dashArray: '8, 8'
                    }).addTo(map);

                    map.fitBounds(polyline.getBounds(), { padding: [50, 50] });
                });
            </script>
        @endif
    </main>

    <!-- Footer: Clean Navy & White -->
    <footer class="bg-slate-900 border-t border-slate-800 py-10 mt-auto text-slate-400">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs space-y-2">
            <p class="font-bold text-white text-sm">CargoTrace &bull; Global Shipment Visibility & Real-Time Tracking</p>
            <p>Web Programming 2 (UAS) &bull; Built with Laravel 12 &bull; Three.js WebGL &bull; Leaflet OSM &bull; Open-Meteo API</p>
            <p class="text-[11px] text-slate-500">Map data &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" class="underline hover:text-white">OpenStreetMap</a> contributors &bull; Weather powered by <a href="https://open-meteo.com/" target="_blank" class="underline hover:text-white">Open-Meteo</a></p>
        </div>
    </footer>

    <!-- Three.js CDN for 3D Globe -->
    @if(!isset($shipment))
        <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
        <script>
            // Helper for quick filling resi into input
            function quickFillTrack(resi) {
                const input = document.getElementById('tracking_input');
                if (input) {
                    input.value = resi;
                    input.focus();
                    if (window.focusRoute) {
                        window.focusRoute(resi);
                    }
                }
            }

            (function initCargoTraceGlobe() {
                const container = document.getElementById('globe-container');
                const canvas = document.getElementById('globe-canvas');
                if (!container || !canvas) return;

                // Fallback check
                if (typeof THREE === 'undefined') {
                    console.warn("Three.js not loaded, rendering 2D canvas radar fallback.");
                    init2DFallback(canvas);
                    return;
                }

                // Global route data injected from Laravel
                const globeRoutes = @json($globeRoutes);

                // --- 1. Scene, Camera, Renderer ---
                const scene = new THREE.Scene();
                const width = container.clientWidth || 600;
                const height = container.clientHeight || 600;
                
                const camera = new THREE.PerspectiveCamera(45, width / height, 1, 2000);
                camera.position.set(0, 20, 270);

                let renderer;
                try {
                    renderer = new THREE.WebGLRenderer({
                        canvas: canvas,
                        antialias: true,
                        alpha: true
                    });
                } catch(e) {
                    console.warn("WebGL initialization failed, falling back to 2D:", e);
                    init2DFallback(canvas);
                    return;
                }

                renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
                renderer.setSize(width, height);

                // Main globe group that rotates
                const globeGroup = new THREE.Group();
                scene.add(globeGroup);

                // --- 2. Lighting: Bright Daylight Orbital Sun ---
                const ambientLight = new THREE.AmbientLight(0xffffff, 1.1);
                scene.add(ambientLight);

                const sunLight = new THREE.DirectionalLight(0xffffff, 1.3);
                sunLight.position.set(300, 250, 400);
                scene.add(sunLight);

                const skyLight = new THREE.DirectionalLight(0x7dd3fc, 0.7);
                skyLight.position.set(-300, -100, -300);
                scene.add(skyLight);

                // --- 3. Soft Ambient Orbit Particles ---
                const starGeo = new THREE.BufferGeometry();
                const starCount = 200;
                const starPos = new Float32Array(starCount * 3);
                for (let i = 0; i < starCount * 3; i += 3) {
                    const r = 320 + Math.random() * 350;
                    const theta = Math.random() * Math.PI * 2;
                    const phi = Math.acos((Math.random() * 2) - 1);
                    starPos[i] = r * Math.sin(phi) * Math.cos(theta);
                    starPos[i+1] = r * Math.sin(phi) * Math.sin(theta);
                    starPos[i+2] = r * Math.cos(phi);
                }
                starGeo.setAttribute('position', new THREE.BufferAttribute(starPos, 3));
                const starMat = new THREE.PointsMaterial({
                    color: 0xffffff,
                    size: 2.0,
                    transparent: true,
                    opacity: 0.6
                });
                scene.add(new THREE.Points(starGeo, starMat));

                // --- 4. Procedural Sky Blue & Cloud White Continents Texture ---
                const globeRadius = 90;
                const texCanvas = document.createElement('canvas');
                texCanvas.width = 2048;
                texCanvas.height = 1024;
                const ctx = texCanvas.getContext('2d');

                // Ocean: Luminous Azure Blue
                ctx.fillStyle = '#0f5db8';
                ctx.fillRect(0, 0, texCanvas.width, texCanvas.height);

                // Subtle latitude and longitude lines
                ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
                ctx.lineWidth = 1;
                for (let lat = -80; lat <= 80; lat += 20) {
                    const y = ((90 - lat) / 180) * texCanvas.height;
                    ctx.beginPath();
                    ctx.moveTo(0, y);
                    ctx.lineTo(texCanvas.width, y);
                    ctx.stroke();
                }
                for (let lng = -180; lng <= 180; lng += 30) {
                    const x = ((lng + 180) / 360) * texCanvas.width;
                    ctx.beginPath();
                    ctx.moveTo(x, 0);
                    ctx.lineTo(x, texCanvas.height);
                    ctx.stroke();
                }

                // Helper to convert lat/lng to canvas pixels
                function toCanvasCoords(lat, lng) {
                    const x = ((lng + 180) / 360) * texCanvas.width;
                    const y = ((90 - lat) / 180) * texCanvas.height;
                    return [x, y];
                }

                // Continent Polygons
                const continents = [
                    // North America
                    [
                        [70, -165], [68, -135], [58, -94], [50, -56], [44, -64], [30, -81], [25, -80],
                        [16, -93], [8, -78], [15, -92], [22, -106], [32, -117], [48, -125], [60, -145],
                        [65, -168], [70, -165]
                    ],
                    // South America
                    [
                        [11, -75], [8, -60], [2, -50], [-5, -35], [-18, -39], [-30, -50], [-45, -65],
                        [-55, -68], [-50, -74], [-35, -73], [-15, -75], [-4, -81], [5, -77], [11, -75]
                    ],
                    // Europe & Western Asia
                    [
                        [71, 28], [68, 50], [55, 60], [45, 50], [40, 28], [36, -5], [43, -9],
                        [48, -4], [55, 8], [60, 5], [68, 14], [71, 28]
                    ],
                    // Africa
                    [
                        [36, 10], [32, 32], [22, 37], [11, 51], [-4, 40], [-25, 33], [-34, 20],
                        [-22, 14], [-5, 12], [5, 2], [5, -10], [15, -17], [28, -13], [35, -5], [36, 10]
                    ],
                    // Asia & Russia
                    [
                        [72, 70], [75, 105], [70, 150], [60, 165], [50, 140], [38, 120], [22, 115],
                        [10, 106], [15, 100], [22, 90], [8, 77], [25, 65], [30, 50], [45, 60],
                        [55, 75], [72, 70]
                    ],
                    // Australia
                    [
                        [-12, 132], [-14, 143], [-25, 153], [-37, 150], [-38, 140], [-35, 117],
                        [-22, 114], [-15, 124], [-12, 132]
                    ],
                    // Indonesian Archipelago (Maritime SE Asia)
                    [
                        [5, 96], [3, 98], [-4, 103], [-6, 106], [-8, 114], [-8, 116], [-5, 106], [1, 100], [5, 96]
                    ],
                    [
                        [6, 116], [4, 118], [-3, 117], [-4, 114], [1, 110], [4, 113], [6, 116]
                    ],
                    [
                        [-1, 131], [-3, 140], [-8, 141], [-6, 135], [-1, 131]
                    ],
                    // Japan
                    [
                        [45, 142], [40, 140], [35, 136], [31, 131], [35, 134], [40, 141], [45, 142]
                    ],
                    // UK & Ireland
                    [
                        [58, -5], [55, -2], [50, 0], [51, -5], [55, -6], [58, -5]
                    ]
                ];

                // Continents: Crisp Cloud White with vibrant Sky Blue border
                ctx.fillStyle = '#ffffff';
                ctx.strokeStyle = '#38bdf8';
                ctx.lineWidth = 3;

                continents.forEach(poly => {
                    ctx.beginPath();
                    poly.forEach((pt, idx) => {
                        const [x, y] = toCanvasCoords(pt[0], pt[1]);
                        if (idx === 0) ctx.moveTo(x, y);
                        else ctx.lineTo(x, y);
                    });
                    ctx.closePath();
                    ctx.fill();
                    ctx.stroke();
                });

                // Subtle cyan matrix dots over landmass
                ctx.fillStyle = 'rgba(14, 165, 233, 0.45)';
                for (let i = 0; i < 3200; i++) {
                    const lat = (Math.random() * 140) - 60;
                    const lng = (Math.random() * 360) - 180;
                    const [x, y] = toCanvasCoords(lat, lng);
                    const pixel = ctx.getImageData(x, y, 1, 1).data;
                    // If pixel is on white continent land
                    if (pixel[0] > 200 && pixel[1] > 200 && pixel[2] > 200) {
                        ctx.beginPath();
                        ctx.arc(x, y, 1.2, 0, Math.PI * 2);
                        ctx.fill();
                    }
                }

                const globeTexture = new THREE.CanvasTexture(texCanvas);

                // Base Globe Sphere Mesh
                const globeGeo = new THREE.SphereGeometry(globeRadius, 64, 64);
                const globeMat = new THREE.MeshPhongMaterial({
                    map: globeTexture,
                    specular: 0xbae6fd,
                    shininess: 25,
                    transparent: true,
                    opacity: 0.98
                });
                const globeMesh = new THREE.Mesh(globeGeo, globeMat);
                globeGroup.add(globeMesh);

                // --- 5. Atmospheric Outer Glow Sphere (Soft White & Sky Blue) ---
                const atmoGeo = new THREE.SphereGeometry(globeRadius * 1.035, 64, 64);
                const atmoMat = new THREE.MeshBasicMaterial({
                    color: 0xffffff,
                    transparent: true,
                    opacity: 0.22,
                    side: THREE.BackSide,
                    blending: THREE.AdditiveBlending
                });
                globeGroup.add(new THREE.Mesh(atmoGeo, atmoMat));

                // Helper: Lat/Lng to 3D Cartesian coordinates on sphere
                function latLngToVector3(lat, lng, r) {
                    const phi = (90 - lat) * (Math.PI / 180);
                    const theta = (lng + 180) * (Math.PI / 180);
                    const x = -r * Math.sin(phi) * Math.cos(theta);
                    const y = r * Math.cos(phi);
                    const z = r * Math.sin(phi) * Math.sin(theta);
                    return new THREE.Vector3(x, y, z);
                }

                // --- 6. Unique Hub Markers & Pulsing Radar Rings ---
                const hubMap = new Map();
                globeRoutes.forEach(r => {
                    if (r.origin && !hubMap.has(r.origin.code)) hubMap.set(r.origin.code, r.origin);
                    if (r.destination && !hubMap.has(r.destination.code)) hubMap.set(r.destination.code, r.destination);
                });

                const ringMeshes = [];
                hubMap.forEach((hub) => {
                    const pos = latLngToVector3(hub.lat, hub.lng, globeRadius);

                    // Beacon Dot: Pure Cloud White
                    const dotGeo = new THREE.SphereGeometry(1.6, 16, 16);
                    const dotMat = new THREE.MeshBasicMaterial({ color: 0xffffff });
                    const dotMesh = new THREE.Mesh(dotGeo, dotMat);
                    dotMesh.position.copy(pos);
                    globeGroup.add(dotMesh);

                    // Outward Beacon Cylinder: Sky Blue
                    const cylGeo = new THREE.CylinderGeometry(0.4, 0.4, 4.5, 8);
                    const cylMat = new THREE.MeshBasicMaterial({ color: 0x38bdf8 });
                    const cylMesh = new THREE.Mesh(cylGeo, cylMat);
                    cylMesh.position.copy(pos.clone().multiplyScalar(1.02));
                    cylMesh.quaternion.setFromUnitVectors(new THREE.Vector3(0, 1, 0), pos.clone().normalize());
                    globeGroup.add(cylMesh);

                    // Pulsing Radar Ring: Cloud White
                    const ringGeo = new THREE.RingGeometry(1.2, 2.2, 32);
                    const ringMat = new THREE.MeshBasicMaterial({
                        color: 0xffffff,
                        side: THREE.DoubleSide,
                        transparent: true,
                        opacity: 0.85
                    });
                    const ringMesh = new THREE.Mesh(ringGeo, ringMat);
                    ringMesh.position.copy(pos.clone().multiplyScalar(1.005));
                    ringMesh.lookAt(pos.clone().multiplyScalar(2));
                    globeGroup.add(ringMesh);

                    ringMeshes.push({
                        mesh: ringMesh,
                        scale: 1 + Math.random() * 0.5,
                        speed: 0.02 + Math.random() * 0.01
                    });
                });

                // --- 7. 3D Trade Arcs & Traveling Pulse Particles ---
                const arcCurves = [];
                const pulseParticles = [];

                globeRoutes.forEach((route, idx) => {
                    const originVec = latLngToVector3(route.origin.lat, route.origin.lng, globeRadius);
                    const destVec = latLngToVector3(route.destination.lat, route.destination.lng, globeRadius);
                    const distance = originVec.distanceTo(destVec);

                    // Midpoint for 3D curved arc
                    const midVec = originVec.clone().add(destVec).multiplyScalar(0.5);
                    const arcHeight = globeRadius + Math.max(14, Math.min(55, distance * 0.28));
                    const ctrlVec = midVec.clone().normalize().multiplyScalar(arcHeight);

                    const curve = new THREE.QuadraticBezierCurve3(originVec, ctrlVec, destVec);
                    arcCurves.push({ resi: route.resi, curve: curve, origin: route.origin, destination: route.destination });

                    // Arc color by status: High contrast
                    let arcColor = 0xffffff; // Pure Cloud White for IN_TRANSIT
                    let pulseColor = 0x38bdf8; // Sky Blue pulse
                    if (route.status === 'EXCEPTION') {
                        arcColor = 0xff3366; // Bright Rose
                        pulseColor = 0xff6699;
                    } else if (route.status === 'DELIVERED') {
                        arcColor = 0x00e676; // Bright Emerald
                        pulseColor = 0x69f0ae;
                    }

                    // Arc Line Mesh
                    const curvePoints = curve.getPoints(60);
                    const lineGeo = new THREE.BufferGeometry().setFromPoints(curvePoints);
                    const lineMat = new THREE.LineBasicMaterial({
                        color: arcColor,
                        transparent: true,
                        opacity: 0.9,
                        linewidth: 2
                    });
                    const lineMesh = new THREE.Line(lineGeo, lineMat);
                    globeGroup.add(lineMesh);

                    // Traveling pulse light packet
                    const pGeo = new THREE.SphereGeometry(1.8, 12, 12);
                    const pMat = new THREE.MeshBasicMaterial({
                        color: pulseColor,
                        transparent: true,
                        opacity: 0.95
                    });
                    const pMesh = new THREE.Mesh(pGeo, pMat);
                    globeGroup.add(pMesh);

                    pulseParticles.push({
                        mesh: pMesh,
                        curve: curve,
                        progress: (idx * 0.2) % 1.0,
                        speed: 0.003 + (idx % 3) * 0.001
                    });
                });

                // Initial orientation: Center on Southeast Asia (Singapore / Indonesia trade corridors)
                globeGroup.rotation.y = -1.2;
                globeGroup.rotation.x = 0.25;

                // --- 8. Smooth Mouse & Touch Interactivity ---
                let isDragging = false;
                let prevMousePos = { x: 0, y: 0 };
                let autoRotate = true;
                let rotateTimeout = null;

                const btnToggle = document.getElementById('btn-toggle-rotate');
                const rotateDot = document.getElementById('rotate-dot');
                const rotateText = document.getElementById('rotate-text');

                function updateRotateState(state) {
                    autoRotate = state;
                    if (rotateDot && rotateText) {
                        if (autoRotate) {
                            rotateDot.className = "w-2 h-2 rounded-full bg-emerald-500 animate-pulse";
                            rotateText.textContent = "Rotate: ON";
                        } else {
                            rotateDot.className = "w-2 h-2 rounded-full bg-slate-400";
                            rotateText.textContent = "Rotate: OFF";
                        }
                    }
                }

                if (btnToggle) {
                    btnToggle.addEventListener('click', () => {
                        updateRotateState(!autoRotate);
                    });
                }

                // Mouse Events
                container.addEventListener('mousedown', (e) => {
                    isDragging = true;
                    prevMousePos = { x: e.clientX, y: e.clientY };
                    if (rotateTimeout) clearTimeout(rotateTimeout);
                });

                window.addEventListener('mouseup', () => {
                    if (isDragging) {
                        isDragging = false;
                        rotateTimeout = setTimeout(() => { updateRotateState(true); }, 4000);
                    }
                });

                window.addEventListener('mousemove', (e) => {
                    if (!isDragging) return;
                    updateRotateState(false);
                    const deltaX = e.clientX - prevMousePos.x;
                    const deltaY = e.clientY - prevMousePos.y;

                    globeGroup.rotation.y += deltaX * 0.006;
                    globeGroup.rotation.x = Math.max(-Math.PI / 3, Math.min(Math.PI / 3, globeGroup.rotation.x + deltaY * 0.006));

                    prevMousePos = { x: e.clientX, y: e.clientY };
                });

                // Wheel Zoom
                container.addEventListener('wheel', (e) => {
                    e.preventDefault();
                    camera.position.z = Math.max(160, Math.min(420, camera.position.z + e.deltaY * 0.25));
                }, { passive: false });

                // Touch Events
                let prevTouchPos = null;
                container.addEventListener('touchstart', (e) => {
                    if (e.touches.length === 1) {
                        isDragging = true;
                        prevTouchPos = { x: e.touches[0].clientX, y: e.touches[0].clientY };
                    }
                });

                container.addEventListener('touchmove', (e) => {
                    if (isDragging && e.touches.length === 1) {
                        updateRotateState(false);
                        const deltaX = e.touches[0].clientX - prevTouchPos.x;
                        const deltaY = e.touches[0].clientY - prevTouchPos.y;
                        globeGroup.rotation.y += deltaX * 0.008;
                        globeGroup.rotation.x = Math.max(-Math.PI / 3, Math.min(Math.PI / 3, globeGroup.rotation.x + deltaY * 0.008));
                        prevTouchPos = { x: e.touches[0].clientX, y: e.touches[0].clientY };
                    }
                });

                container.addEventListener('touchend', () => {
                    isDragging = false;
                    rotateTimeout = setTimeout(() => { updateRotateState(true); }, 4000);
                });

                // Zoom Buttons
                const btnZoomIn = document.getElementById('btn-zoom-in');
                const btnZoomOut = document.getElementById('btn-zoom-out');
                const btnReset = document.getElementById('btn-reset-view');

                if (btnZoomIn) {
                    btnZoomIn.addEventListener('click', () => {
                        camera.position.z = Math.max(160, camera.position.z - 35);
                    });
                }
                if (btnZoomOut) {
                    btnZoomOut.addEventListener('click', () => {
                        camera.position.z = Math.min(420, camera.position.z + 35);
                    });
                }
                if (btnReset) {
                    btnReset.addEventListener('click', () => {
                        camera.position.set(0, 20, 270);
                        globeGroup.rotation.set(0.25, -1.2, 0);
                        updateRotateState(true);
                    });
                }

                // Focus on specific route (Global exposed)
                window.focusRoute = function(resi) {
                    const match = arcCurves.find(a => a.resi === resi);
                    if (!match) return;

                    updateRotateState(false);
                    const midLng = (match.origin.lng + match.destination.lng) / 2;
                    const midLat = (match.origin.lat + match.destination.lat) / 2;

                    const targetY = -((midLng + 90) * (Math.PI / 180));
                    const targetX = (midLat * 0.4) * (Math.PI / 180);

                    globeGroup.rotation.y = targetY;
                    globeGroup.rotation.x = targetX;
                    camera.position.z = 240;

                    if (rotateTimeout) clearTimeout(rotateTimeout);
                    rotateTimeout = setTimeout(() => { updateRotateState(true); }, 6000);
                };

                // Responsive Window Resize
                window.addEventListener('resize', () => {
                    const newW = container.clientWidth;
                    const newH = container.clientHeight;
                    camera.aspect = newW / newH;
                    camera.updateProjectionMatrix();
                    renderer.setSize(newW, newH);
                });

                // --- 9. Animation Loop ---
                let clock = new THREE.Clock();
                function animate() {
                    requestAnimationFrame(animate);

                    const delta = clock.getDelta();

                    // Auto-rotation
                    if (autoRotate && !isDragging) {
                        globeGroup.rotation.y += 0.0018;
                    }

                    // Animate pulsing radar rings at ports
                    ringMeshes.forEach(r => {
                        r.scale += r.speed;
                        if (r.scale > 2.6) {
                            r.scale = 1.0;
                            r.mesh.material.opacity = 0.85;
                        } else {
                            r.mesh.scale.set(r.scale, r.scale, r.scale);
                            r.mesh.material.opacity = Math.max(0, 0.85 * (1 - (r.scale - 1) / 1.6));
                        }
                    });

                    // Animate traveling pulse light packets along curves
                    pulseParticles.forEach(p => {
                        p.progress += p.speed;
                        if (p.progress > 1.0) p.progress = 0;
                        const pos = p.curve.getPoint(p.progress);
                        p.mesh.position.copy(pos);
                    });

                    renderer.render(scene, camera);
                }

                animate();
            })();

            // 2D Canvas Radar Fallback for environments where WebGL is unsupported
            function init2DFallback(canvas) {
                const ctx = canvas.getContext('2d');
                canvas.width = canvas.parentElement.clientWidth || 600;
                canvas.height = canvas.parentElement.clientHeight || 500;
                
                function drawRadar() {
                    ctx.fillStyle = '#177be5';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    const cx = canvas.width / 2;
                    const cy = canvas.height / 2;
                    const maxR = Math.min(cx, cy) * 0.85;

                    ctx.strokeStyle = '#ffffff';
                    ctx.lineWidth = 1;
                    for (let r = 0.25; r <= 1; r += 0.25) {
                        ctx.beginPath();
                        ctx.arc(cx, cy, maxR * r, 0, Math.PI * 2);
                        ctx.stroke();
                    }
                    ctx.fillStyle = '#ffffff';
                    ctx.font = 'bold 14px monospace';
                    ctx.textAlign = 'center';
                    ctx.fillText('CargoTrace Radar Telemetry Active', cx, cy - 20);
                }
                drawRadar();
            }
        </script>
    @endif

</body>
</html>