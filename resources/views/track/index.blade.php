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
        
        /* Custom scrollbar for cyber HUD */
        .hud-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .hud-scroll::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.6); }
        .hud-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        .hud-scroll::-webkit-scrollbar-thumb:hover { background: #475569; }

        @keyframes pulse-slow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.08); }
        }
        .animate-pulse-slow {
            animation: pulse-slow 3s infinite ease-in-out;
        }

        @keyframes radar-sweep {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .animate-radar {
            animation: radar-sweep 8s linear infinite;
        }
    </style>
</head>
<body class="bg-slate-950 font-sans text-slate-100 min-h-screen flex flex-col selection:bg-cyan-500 selection:text-slate-950">

    <!-- Navbar -->
    <nav class="bg-slate-950/90 backdrop-blur-md border-b border-slate-800/80 sticky top-0 z-50 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('track.index') }}" class="text-2xl font-black text-white tracking-tight flex items-center gap-1.5 group">
                    <span class="p-1.5 rounded-lg bg-gradient-to-tr from-indigo-600 to-cyan-500 text-white shadow-md shadow-indigo-500/20 group-hover:scale-105 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </span>
                    <span>CargoTrace<span class="text-cyan-400">.</span></span>
                </a>
                <span class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-cyan-950/70 text-cyan-300 border border-cyan-800/50">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span>
                    Live 3D Telemetry
                </span>
            </div>
            
            <div class="flex items-center gap-3 sm:gap-4">
                <a href="#active-routes" class="hidden sm:inline-block text-xs font-semibold text-slate-300 hover:text-white transition">
                    Trade Corridors
                </a>
                <a href="#platform-specs" class="hidden sm:inline-block text-xs font-semibold text-slate-300 hover:text-white transition">
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
                        $roleBadgeColor = match($userRole) {
                            'ADMIN' => 'bg-purple-900/60 text-purple-300 border-purple-700/50',
                            'OPERATOR' => 'bg-amber-900/60 text-amber-300 border-amber-700/50',
                            default => 'bg-emerald-900/60 text-emerald-300 border-emerald-700/50',
                        };
                    @endphp
                    <div class="flex items-center gap-2">
                        <span class="hidden lg:inline-block px-2 py-0.5 text-[10px] font-mono font-bold rounded border {{ $roleBadgeColor }}">
                            {{ $userRole }}
                        </span>
                        <a href="{{ $dashRoute }}" class="text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/30 px-4 py-2 rounded-xl transition flex items-center gap-1.5">
                            <span>Dashboard</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-bold text-slate-200 hover:text-white bg-slate-900 hover:bg-slate-800 border border-slate-700/80 px-4 py-2 rounded-xl transition">
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
            <!-- 3D GLOBE HERO EXPERIENCE                                 -->
            <!-- ======================================================== -->
            <section class="relative min-h-[90vh] lg:min-h-[92vh] flex flex-col justify-center overflow-hidden bg-gradient-to-b from-slate-950 via-[#070b14] to-slate-950 border-b border-slate-800/60">
                
                <!-- Ambient Glow Behind Globe -->
                <div class="absolute right-0 top-1/2 -translate-y-1/2 w-[700px] h-[700px] bg-gradient-to-tr from-indigo-600/10 via-cyan-500/15 to-transparent rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute left-1/4 top-10 w-96 h-96 bg-indigo-900/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center flex-grow">
                    
                    <!-- Left Hero Content & Search Box (5 cols) -->
                    <div class="lg:col-span-5 flex flex-col justify-center space-y-6">
                        
                        <!-- Telemetry Status Tag -->
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/90 border border-slate-700/80 text-xs font-medium text-slate-300 w-fit backdrop-blur shadow-sm">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="font-mono text-[11px] uppercase tracking-wider text-cyan-300">Telemetry Active</span>
                            <span class="text-slate-600">•</span>
                            <span class="text-[11px] text-slate-400">{{ count($globeRoutes) }} Global Trade Routes</span>
                        </div>

                        <!-- Main Headline -->
                        <div>
                            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-[1.15]">
                                Real-Time Global <br>
                                <span class="bg-gradient-to-r from-cyan-400 via-indigo-300 to-indigo-500 bg-clip-text text-transparent">Cargo Visibility</span>
                            </h1>
                            <p class="mt-4 text-slate-400 text-sm sm:text-base leading-relaxed">
                                Track multimodal intercontinental shipments across maritime, air, and ground corridors. Inspect active 3D trade arcs, weather checkpoints, and deterministic ETA telemetry.
                            </p>
                        </div>

                        <!-- Tracking Search Box -->
                        <div class="bg-slate-900/80 backdrop-blur-md p-2.5 rounded-2xl border border-slate-700/80 shadow-2xl">
                            <form action="{{ route('track.search') }}" method="POST" class="flex flex-col sm:flex-row gap-2">
                                @csrf
                                <div class="relative flex-grow">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                    <input type="text" name="tracking_number" id="tracking_input" value="{{ old('tracking_number') }}" 
                                        placeholder="ENTER RESI: CT-20261118-SEAEUR" required 
                                        class="w-full pl-11 pr-4 py-3.5 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/20 outline-none transition font-mono font-bold tracking-wider uppercase text-sm">
                                </div>
                                <button type="submit" class="py-3.5 px-6 bg-gradient-to-r from-indigo-600 to-cyan-600 hover:from-indigo-500 hover:to-cyan-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
                                    <span>Track Cargo</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </button>
                            </form>

                            @if(session('error'))
                                <div class="mt-3 p-3 bg-rose-950/80 border border-rose-800/80 text-rose-300 rounded-xl text-xs font-semibold flex items-center gap-2">
                                    <svg class="w-4 h-4 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span>{{ session('error') }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Quick-Click Sample Shipments -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                                <span class="uppercase tracking-wider">Quick Track Scenarios:</span>
                                <span class="text-cyan-400/80">Click to fill & trace</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="quickFillTrack('CT-20261118-SEAEUR')" 
                                    class="group text-left px-3 py-1.5 rounded-lg bg-slate-900/90 hover:bg-slate-800 border border-slate-700/80 hover:border-cyan-500/50 transition">
                                    <span class="block text-[11px] font-mono font-bold text-cyan-300">CT-20261118-SEAEUR</span>
                                    <span class="block text-[10px] text-slate-400">Singapore ➔ Rotterdam (Transit)</span>
                                </button>
                                
                                <button type="button" onclick="quickFillTrack('CT-20261118-ASIANAM')" 
                                    class="group text-left px-3 py-1.5 rounded-lg bg-slate-900/90 hover:bg-slate-800 border border-slate-700/80 hover:border-rose-500/50 transition">
                                    <span class="block text-[11px] font-mono font-bold text-rose-300">CT-20261118-ASIANAM</span>
                                    <span class="block text-[10px] text-slate-400">Shanghai ➔ LA (Delay Alert)</span>
                                </button>
                                
                                <button type="button" onclick="quickFillTrack('CT-20261118-SEAAUS')" 
                                    class="group text-left px-3 py-1.5 rounded-lg bg-slate-900/90 hover:bg-slate-800 border border-slate-700/80 hover:border-emerald-500/50 transition">
                                    <span class="block text-[11px] font-mono font-bold text-emerald-300">CT-20261118-SEAAUS</span>
                                    <span class="block text-[10px] text-slate-400">Jakarta ➔ Sydney (Active)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Live Telemetry Stats Counters -->
                        <div class="grid grid-cols-4 gap-2 pt-2 border-t border-slate-800/80">
                            <div class="p-2.5 rounded-xl bg-slate-900/60 border border-slate-800 text-center">
                                <div class="text-lg font-black font-mono text-white">{{ $stats['total'] }}</div>
                                <div class="text-[10px] font-medium text-slate-400 uppercase tracking-tight">Total</div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-900/60 border border-slate-800 text-center">
                                <div class="text-lg font-black font-mono text-cyan-400">{{ $stats['in_transit'] }}</div>
                                <div class="text-[10px] font-medium text-cyan-300/80 uppercase tracking-tight">Transit</div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-900/60 border border-slate-800 text-center">
                                <div class="text-lg font-black font-mono text-rose-400">{{ $stats['exceptions'] }}</div>
                                <div class="text-[10px] font-medium text-rose-300/80 uppercase tracking-tight">Delays</div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-900/60 border border-slate-800 text-center">
                                <div class="text-lg font-black font-mono text-emerald-400">{{ $stats['delivered'] }}</div>
                                <div class="text-[10px] font-medium text-emerald-300/80 uppercase tracking-tight">Arrived</div>
                            </div>
                        </div>

                    </div>

                    <!-- Right 3D Interactive WebGL Globe Stage (7 cols) -->
                    <div class="lg:col-span-7 relative h-[520px] sm:h-[620px] lg:h-[720px] w-full flex items-center justify-center">
                        
                        <!-- 3D Canvas Container -->
                        <div id="globe-container" class="w-full h-full relative cursor-grab active:cursor-grabbing select-none">
                            <canvas id="globe-canvas" class="w-full h-full block"></canvas>
                        </div>

                        <!-- 3D Globe HUD Overlay: Top Right Controls -->
                        <div class="absolute top-4 right-4 z-20 flex flex-col gap-2">
                            <button id="btn-toggle-rotate" type="button" title="Toggle Auto-Rotation"
                                class="px-3 py-1.5 bg-slate-900/80 hover:bg-slate-800 text-slate-200 border border-slate-700/80 rounded-xl text-xs font-mono font-semibold backdrop-blur shadow-lg transition flex items-center gap-1.5">
                                <span id="rotate-dot" class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                                <span id="rotate-text">Rotate: ON</span>
                            </button>
                            <div class="flex gap-1 justify-end">
                                <button id="btn-zoom-in" type="button" title="Zoom In"
                                    class="w-8 h-8 bg-slate-900/80 hover:bg-slate-800 text-slate-200 border border-slate-700/80 rounded-xl text-sm font-bold backdrop-blur flex items-center justify-center transition">
                                    +
                                </button>
                                <button id="btn-zoom-out" type="button" title="Zoom Out"
                                    class="w-8 h-8 bg-slate-900/80 hover:bg-slate-800 text-slate-200 border border-slate-700/80 rounded-xl text-sm font-bold backdrop-blur flex items-center justify-center transition">
                                    −
                                </button>
                                <button id="btn-reset-view" type="button" title="Reset View"
                                    class="px-2.5 h-8 bg-slate-900/80 hover:bg-slate-800 text-slate-300 border border-slate-700/80 rounded-xl text-xs font-mono backdrop-blur flex items-center justify-center transition">
                                    Reset
                                </button>
                            </div>
                        </div>

                        <!-- Arc Status Legend: Bottom Left of 3D Stage -->
                        <div class="absolute bottom-4 left-4 z-20 bg-slate-950/80 backdrop-blur-md border border-slate-800 px-3.5 py-2.5 rounded-xl shadow-lg flex items-center gap-4 text-[11px] font-mono">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-1 rounded bg-cyan-400 shadow-sm shadow-cyan-400"></span>
                                <span class="text-slate-300">In Transit</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-1 rounded bg-rose-500 shadow-sm shadow-rose-500"></span>
                                <span class="text-slate-300">Exception</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-1 rounded bg-emerald-400 shadow-sm shadow-emerald-400"></span>
                                <span class="text-slate-300">Delivered</span>
                            </div>
                        </div>

                        <!-- Active Route Mini Ticker Drawer: Bottom Right -->
                        <div class="absolute bottom-4 right-4 z-20 max-w-xs hidden sm:block">
                            <div class="bg-slate-900/90 backdrop-blur-md border border-slate-800 rounded-2xl p-3 shadow-xl">
                                <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-800 text-[10px] font-mono text-slate-400 uppercase tracking-wider">
                                    <span>Active Global Hubs</span>
                                    <span class="text-cyan-400 font-bold">10 Ports</span>
                                </div>
                                <div class="space-y-1.5 max-h-28 overflow-y-auto hud-scroll pr-1">
                                    @foreach(collect($globeRoutes)->take(4) as $rt)
                                        <div onclick="focusRoute('{{ $rt['resi'] }}')" class="flex items-center justify-between text-[11px] p-1.5 rounded-lg hover:bg-slate-800/80 cursor-pointer transition">
                                            <div class="flex items-center gap-1.5">
                                                @php
                                                    $pillColor = match($rt['status']) {
                                                        'DELIVERED' => 'bg-emerald-400',
                                                        'EXCEPTION' => 'bg-rose-400',
                                                        default => 'bg-cyan-400',
                                                    };
                                                @endphp
                                                <span class="w-1.5 h-1.5 rounded-full {{ $pillColor }}"></span>
                                                <span class="font-mono font-bold text-slate-200">{{ $rt['origin']['code'] }} ➔ {{ $rt['destination']['code'] }}</span>
                                            </div>
                                            <span class="text-[10px] text-cyan-300 font-mono hover:underline">Focus</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Subtle Bottom Feature Strip -->
                <div class="relative z-10 border-t border-slate-900 bg-slate-950/70 backdrop-blur py-3 text-center text-xs text-slate-400 font-mono">
                    <div class="max-w-7xl mx-auto px-4 flex flex-wrap justify-center items-center gap-4 sm:gap-8">
                        <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-cyan-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> 3D WebGL Spherical Projection</span>
                        <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-cyan-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Deterministic Delay Categorization</span>
                        <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-cyan-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Live Open-Meteo Satellite API</span>
                        <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-cyan-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> OpenStreetMap Routing Engine</span>
                    </div>
                </div>

            </section>

            <!-- ======================================================== -->
            <!-- ACTIVE TRADE CORRIDORS GRID                              -->
            <!-- ======================================================== -->
            <section id="active-routes" class="py-16 bg-slate-900/60 border-b border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
                        <div>
                            <span class="text-xs font-mono font-bold uppercase tracking-widest text-cyan-400">Intercontinental Corridors</span>
                            <h2 class="text-2xl sm:text-3xl font-black text-white mt-1">Monitored Global Shipments</h2>
                            <p class="text-sm text-slate-400 mt-1 max-w-xl">Live shipment pipelines configured in accordance with the Web Programming 2 final project specifications.</p>
                        </div>
                        <div class="mt-4 md:mt-0 flex items-center gap-2 text-xs font-mono text-slate-400">
                            <span>Showing {{ count($allShipments) }} Active Pipelines</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($allShipments as $s)
                            @php
                                $statusBadgeClass = match($s->status) {
                                    'DELIVERED' => 'bg-emerald-950 text-emerald-300 border-emerald-800',
                                    'EXCEPTION' => 'bg-rose-950 text-rose-300 border-rose-800',
                                    'IN_TRANSIT' => 'bg-cyan-950 text-cyan-300 border-cyan-800',
                                    default => 'bg-slate-800 text-slate-300 border-slate-700',
                                };
                            @endphp
                            <div class="bg-slate-950/80 rounded-2xl border border-slate-800 p-6 flex flex-col justify-between hover:border-slate-700 transition shadow-lg group">
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="font-mono text-xs font-bold text-slate-300 group-hover:text-cyan-400 transition">{{ $s->shipment_number }}</span>
                                        <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold rounded-full border uppercase {{ $statusBadgeClass }}">
                                            {{ $s->status }}
                                        </span>
                                    </div>

                                    <!-- Hub to Hub Direction -->
                                    <div class="my-4 p-3 bg-slate-900/80 rounded-xl border border-slate-800/80">
                                        <div class="flex items-center justify-between text-xs">
                                            <div class="text-left">
                                                <div class="text-[10px] text-slate-500 uppercase font-mono">Origin</div>
                                                <div class="font-bold text-white">{{ $s->origin?->city ?? 'Origin' }}</div>
                                                <div class="text-[10px] text-slate-400 font-mono">{{ $s->origin?->code }}</div>
                                            </div>
                                            <div class="px-3 flex flex-col items-center">
                                                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-[10px] text-slate-500 uppercase font-mono">Destination</div>
                                                <div class="font-bold text-white">{{ $s->destination?->city ?? 'Destination' }}</div>
                                                <div class="text-[10px] text-slate-400 font-mono">{{ $s->destination?->code }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5 text-xs text-slate-400">
                                        <div class="flex justify-between">
                                            <span>Carrier:</span>
                                            <span class="font-medium text-slate-200">{{ $s->carrier?->name }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Cargo:</span>
                                            <span class="font-medium text-slate-200">{{ $s->cargo_type }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Gross Weight:</span>
                                            <span class="font-mono text-slate-200">{{ number_format($s->weight_kg, 0) }} kg</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-between">
                                    <form action="{{ route('track.search') }}" method="POST" class="w-full">
                                        @csrf
                                        <input type="hidden" name="tracking_number" value="{{ $s->shipment_number }}">
                                        <button type="submit" class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 border border-slate-700/80 hover:border-cyan-500/50 text-cyan-300 font-bold rounded-xl text-xs transition flex items-center justify-center gap-2">
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
            <section id="platform-specs" class="py-16 bg-slate-950 text-slate-300">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    <div class="text-center max-w-2xl mx-auto mb-12">
                        <span class="text-xs font-mono font-bold uppercase tracking-widest text-indigo-400">Architecture Overview</span>
                        <h2 class="text-3xl font-black text-white mt-1">Engineered for Enterprise Visibility</h2>
                        <p class="text-sm text-slate-400 mt-2">Comprehensive stack built with Laravel 12, SQLite, Three.js WebGL, Leaflet OSM, Open-Meteo REST API, and RBAC security.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800 space-y-3">
                            <div class="w-10 h-10 rounded-xl bg-cyan-950 text-cyan-400 border border-cyan-800 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <h3 class="text-base font-bold text-white">Deterministic Delay Engine</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Automatically categorizes transit deviations into LOW (+6h), MEDIUM (+18h), and HIGH (+48h) severity, dynamically updating expected arrival times.
                            </p>
                        </div>

                        <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800 space-y-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-950 text-indigo-400 border border-indigo-800 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                            </div>
                            <h3 class="text-base font-bold text-white">Live Open-Meteo Satellite Data</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Real-time atmospheric conditions, wind speed, and meteorological telemetry queried at active ocean coordinates without requiring API keys.
                            </p>
                        </div>

                        <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800 space-y-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-950 text-emerald-400 border border-emerald-800 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <h3 class="text-base font-bold text-white">Role-Based Access Control</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
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
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-900 p-6 rounded-2xl shadow-xl border border-slate-800">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest">Shipment Resi</span>
                            @php
                                $statusBadge = match($shipment->status) {
                                    'DELIVERED' => 'bg-emerald-950 text-emerald-300 border-emerald-800',
                                    'IN_TRANSIT' => 'bg-cyan-950 text-cyan-300 border-cyan-800',
                                    'EXCEPTION' => 'bg-rose-950 text-rose-300 border-rose-800',
                                    default => 'bg-slate-800 text-slate-300 border-slate-700',
                                };
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-black border uppercase {{ $statusBadge }}">{{ $shipment->status }}</span>
                        </div>
                        <h2 class="text-2xl font-black font-mono text-white tracking-tight">{{ $shipment->shipment_number }}</h2>
                    </div>

                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <a href="{{ route('track.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl text-xs transition flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            <span>Back to 3D Globe</span>
                        </a>

                        <form action="{{ route('track.search') }}" method="POST" class="flex w-full md:w-auto gap-2">
                            @csrf
                            <input type="text" name="tracking_number" placeholder="Track another ID..." required class="px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 focus:border-cyan-400 outline-none text-xs font-mono font-bold uppercase text-white w-full md:w-60">
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition">Search</button>
                        </form>
                    </div>
                </div>

                <!-- ETA & Delay Status Alert Box -->
                @if(isset($delayStatus))
                    @if($delayStatus['is_delayed'])
                        <div class="bg-amber-950/70 border border-amber-700/80 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-3.5">
                                <div class="p-2.5 bg-amber-900/80 text-amber-300 rounded-xl mt-0.5 border border-amber-700">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-black text-amber-300 uppercase tracking-wide">Transit Delay Detected ({{ $delayStatus['severity'] }} Severity)</h4>
                                        <span class="px-2 py-0.5 bg-amber-800 text-amber-100 rounded text-[10px] font-mono font-bold">+{{ $delayStatus['delay_hours'] }} Hours</span>
                                    </div>
                                    <p class="text-xs text-amber-200 mt-1">{{ $delayStatus['message'] }}</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono font-semibold text-amber-300 bg-slate-950 px-3 py-1.5 rounded-lg border border-amber-800">
                                Expected: {{ \Carbon\Carbon::parse($delayStatus['expected_arrival'])->format('d M Y, H:i') }}
                            </span>
                        </div>
                    @else
                        <div class="bg-emerald-950/70 border border-emerald-800 rounded-2xl p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-emerald-900/80 text-emerald-300 rounded-lg border border-emerald-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-emerald-300 uppercase">On Schedule</h4>
                                    <p class="text-xs text-emerald-200">{{ $delayStatus['message'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Horizontal Lifecycle Progress -->
                <div class="bg-slate-900 p-6 sm:p-8 rounded-2xl shadow-xl border border-slate-800">
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest">Shipment Journey Progress</span>
                        <span class="text-xs font-mono font-bold text-cyan-400">{{ $shipment->progress_percent }}% Completed</span>
                    </div>

                    <div class="relative w-full max-w-4xl mx-auto my-6">
                        <div class="w-full bg-slate-950 h-2.5 rounded-full overflow-hidden border border-slate-800">
                            <div class="h-full bg-gradient-to-r from-indigo-500 to-cyan-400 rounded-full transition-all duration-700" style="width: {{ $shipment->progress_percent }}%;"></div>
                        </div>

                        <div class="flex justify-between items-center text-[11px] font-mono font-bold text-slate-500 mt-3">
                            <span class="{{ $shipment->progress_percent >= 10 ? 'text-cyan-400' : '' }}">Created</span>
                            <span class="{{ $shipment->progress_percent >= 30 ? 'text-cyan-400' : '' }}">Booked</span>
                            <span class="{{ $shipment->progress_percent >= 50 ? 'text-cyan-400' : '' }}">Picked Up</span>
                            <span class="{{ $shipment->progress_percent >= 75 ? 'text-cyan-400' : '' }}">In Transit</span>
                            <span class="{{ $shipment->progress_percent >= 100 ? 'text-emerald-400' : '' }}">Delivered</span>
                        </div>
                    </div>
                </div>

                <!-- Main Grid: Leaflet Map & Timeline / Weather -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Map Column (2/3 width) -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-slate-900 rounded-2xl shadow-xl border border-slate-800 overflow-hidden">
                            <div class="p-4 border-b border-slate-800 bg-slate-950/60 flex justify-between items-center">
                                <div>
                                    <h3 class="text-sm font-bold text-white">Geographic Routing & Waypoints</h3>
                                    <p class="text-[11px] text-slate-400">Live coordinates plotted on OpenStreetMap.</p>
                                </div>
                                <span class="text-[10px] font-mono bg-cyan-950 text-cyan-300 border border-cyan-800 font-bold px-2 py-1 rounded">Leaflet OSM</span>
                            </div>
                            <div id="map" class="w-full bg-slate-950" style="height: 480px; z-index: 1;"></div>
                        </div>

                        <!-- Chronological Tracking Timeline -->
                        <div class="bg-slate-900 rounded-2xl shadow-xl border border-slate-800 p-6">
                            <div class="flex justify-between items-center mb-6 border-b border-slate-800 pb-4">
                                <div>
                                    <h3 class="text-base font-bold text-white">Tracking Event Timeline</h3>
                                    <p class="text-xs text-slate-400">Immutable audit history of all recorded cargo scan events.</p>
                                </div>
                                <span class="text-xs font-mono font-semibold px-2.5 py-1 bg-slate-800 text-slate-300 rounded-lg border border-slate-700">
                                    {{ $shipment->chronologicalEvents->count() }} Milestones
                                </span>
                            </div>

                            <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-800">
                                @forelse($shipment->chronologicalEvents->reverse() as $event)
                                    @php
                                        $isEventException = $event->status === 'EXCEPTION';
                                        $dotColor = $isEventException ? 'bg-rose-500 ring-rose-950' : ($event->status === 'DELIVERED' ? 'bg-emerald-500 ring-emerald-950' : 'bg-cyan-500 ring-cyan-950');
                                    @endphp
                                    <div class="relative">
                                        <div class="absolute -left-[27px] top-1.5 w-3.5 h-3.5 rounded-full {{ $dotColor }} ring-4 bg-slate-950"></div>
                                        <div class="bg-slate-950 p-4 rounded-xl border border-slate-800">
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-mono font-black uppercase tracking-wider {{ $isEventException ? 'text-rose-400' : 'text-cyan-300' }}">
                                                        {{ $event->status }}
                                                    </span>
                                                    @if($event->location)
                                                        <span class="text-xs font-medium text-slate-300">• {{ $event->location->name }} ({{ $event->location->city }})</span>
                                                    @endif
                                                </div>
                                                <span class="text-[11px] font-mono text-slate-400">
                                                    {{ \Carbon\Carbon::parse($event->event_time)->format('d M Y, H:i') }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-400 leading-relaxed">{{ $event->description }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-500 italic">No tracking events recorded yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Side Cards: Route, Cargo & Live Weather (1/3 width) -->
                    <div class="lg:col-span-1 space-y-6">
                        
                        <!-- Live Weather Card (Open-Meteo) -->
                        @if(isset($weather) && $weather['success'])
                            <div class="bg-gradient-to-br from-indigo-950 via-slate-900 to-slate-950 text-white p-6 rounded-2xl shadow-xl border border-indigo-800/60 relative overflow-hidden">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-cyan-400">Live Weather Telemetry</span>
                                        <h4 class="text-base font-bold mt-0.5">Current Checkpoint</h4>
                                    </div>
                                    <span class="text-3xl">{{ $weather['icon'] }}</span>
                                </div>
                                <div class="flex items-baseline gap-2 mb-3">
                                    <span class="text-3xl font-black font-mono">{{ $weather['temperature'] }}°C</span>
                                    <span class="text-xs text-cyan-200 font-medium">{{ $weather['condition'] }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-[11px] font-mono text-slate-400 pt-3 border-t border-slate-800">
                                    <div>Wind: <b class="text-white">{{ $weather['windspeed'] }} km/h</b></div>
                                    <div class="text-right">Source: <b class="text-white">{{ $weather['source'] }}</b></div>
                                </div>
                            </div>
                        @endif

                        <!-- Route Hubs Card -->
                        <div class="bg-slate-900 p-6 rounded-2xl shadow-xl border border-slate-800 space-y-4">
                            <h4 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest">Route Schedule</h4>
                            <div>
                                <p class="text-[10px] text-slate-500 font-mono font-bold uppercase">Origin Port / Hub</p>
                                <p class="text-sm font-bold text-white">{{ $shipment->origin?->name ?? 'N/A' }}</p>
                                <p class="text-xs text-slate-400">{{ $shipment->origin?->city }}, {{ $shipment->origin?->country }}</p>
                            </div>
                            <div class="border-t border-slate-800 pt-3">
                                <p class="text-[10px] text-slate-500 font-mono font-bold uppercase">Destination Port / Hub</p>
                                <p class="text-sm font-bold text-white">{{ $shipment->destination?->name ?? 'N/A' }}</p>
                                <p class="text-xs text-slate-400">{{ $shipment->destination?->city }}, {{ $shipment->destination?->country }}</p>
                            </div>
                        </div>

                        <!-- Cargo Details Card -->
                        <div class="bg-slate-900 p-6 rounded-2xl shadow-xl border border-slate-800 space-y-3">
                            <h4 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest">Cargo & Carrier Specifications</h4>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400">Carrier Partner:</span>
                                <span class="font-bold text-white">{{ $shipment->carrier?->name }} ({{ $shipment->carrier?->code }})</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400">Cargo Type:</span>
                                <span class="font-bold text-white">{{ $shipment->cargo_type ?? 'General Freight' }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400">Gross Weight:</span>
                                <span class="font-mono font-bold text-white">{{ number_format($shipment->weight_kg, 2) }} kg</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400">Expected Arrival:</span>
                                <span class="font-mono font-bold text-cyan-400">
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
                        html: '<div style="background:#4f46e5; color:#fff; width:22px; height:22px; border-radius:50%; border:3px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:bold;">A</div>',
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
                            var cpColor = isExc ? '#f43f5e' : '#06b6d4';
                            var cpIcon = L.divIcon({
                                className: 'cp-marker',
                                html: '<div style="background:'+cpColor+'; width:14px; height:14px; border-radius:50%; border:2px solid #fff; box-shadow:0 2px 4px rgba(0,0,0,0.5);"></div>',
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
                        html: '<div style="background:#10b981; color:#fff; width:22px; height:22px; border-radius:50%; border:3px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:bold;">B</div>',
                        iconSize: [22, 22],
                        iconAnchor: [11, 11]
                    });
                    L.marker([destLat, destLng], { icon: destIcon }).addTo(map)
                        .bindPopup('<b>Destination:</b> {{ $shipment->destination?->name }} ({{ $shipment->destination?->city }})');

                    // Draw Route Polyline
                    var polyline = L.polyline(waypoints, {
                        color: '#06b6d4',
                        weight: 4,
                        opacity: 0.9,
                        dashArray: '8, 8'
                    }).addTo(map);

                    map.fitBounds(polyline.getBounds(), { padding: [50, 50] });
                });
            </script>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 border-t border-slate-900 py-8 mt-auto text-slate-500">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs space-y-2">
            <p class="font-bold text-slate-400">CargoTrace &bull; Global Shipment Visibility & Real-Time Tracking Platform</p>
            <p>Web Programming 2 (UAS) &bull; Built with Laravel 12 &bull; Three.js WebGL &bull; Leaflet OSM &bull; Open-Meteo API</p>
            <p class="text-[11px] text-slate-600">Map data &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" class="underline hover:text-slate-400">OpenStreetMap</a> contributors &bull; Weather powered by <a href="https://open-meteo.com/" target="_blank" class="underline hover:text-slate-400">Open-Meteo</a></p>
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
                    // Optional instant focus or feedback
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

                // --- 2. Lighting ---
                const ambientLight = new THREE.AmbientLight(0xffffff, 0.85);
                scene.add(ambientLight);

                const dirLight = new THREE.DirectionalLight(0x67e8f9, 1.4);
                dirLight.position.set(300, 200, 400);
                scene.add(dirLight);

                const backLight = new THREE.DirectionalLight(0x4338ca, 0.8);
                backLight.position.set(-300, -200, -300);
                scene.add(backLight);

                // --- 3. Starfield Background Particles ---
                const starGeo = new THREE.BufferGeometry();
                const starCount = 350;
                const starPos = new Float32Array(starCount * 3);
                for (let i = 0; i < starCount * 3; i += 3) {
                    const r = 350 + Math.random() * 400;
                    const theta = Math.random() * Math.PI * 2;
                    const phi = Math.acos((Math.random() * 2) - 1);
                    starPos[i] = r * Math.sin(phi) * Math.cos(theta);
                    starPos[i+1] = r * Math.sin(phi) * Math.sin(theta);
                    starPos[i+2] = r * Math.cos(phi);
                }
                starGeo.setAttribute('position', new THREE.BufferAttribute(starPos, 3));
                const starMat = new THREE.PointsMaterial({
                    color: 0x94a3b8,
                    size: 1.5,
                    transparent: true,
                    opacity: 0.5
                });
                scene.add(new THREE.Points(starGeo, starMat));

                // --- 4. Procedural Continents Canvas Texture (100% Offline & Instant) ---
                const globeRadius = 90;
                const texCanvas = document.createElement('canvas');
                texCanvas.width = 2048;
                texCanvas.height = 1024;
                const ctx = texCanvas.getContext('2d');

                // Ocean deep color
                ctx.fillStyle = '#060a13';
                ctx.fillRect(0, 0, texCanvas.width, texCanvas.height);

                // Draw Latitude & Longitude Grid Lines
                ctx.strokeStyle = 'rgba(30, 41, 59, 0.5)';
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

                // Draw Continent Polygons
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

                ctx.fillStyle = '#111c2e';
                ctx.strokeStyle = '#0284c7';
                ctx.lineWidth = 2.5;

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

                // Dotted cyber effect across landmasses
                ctx.fillStyle = 'rgba(56, 189, 248, 0.4)';
                for (let i = 0; i < 3000; i++) {
                    const lat = (Math.random() * 140) - 60;
                    const lng = (Math.random() * 360) - 180;
                    const [x, y] = toCanvasCoords(lat, lng);
                    const pixel = ctx.getImageData(x, y, 1, 1).data;
                    // If pixel is on continent land
                    if (pixel[0] > 10 && pixel[2] > 30) {
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
                    specular: 0x0369a1,
                    shininess: 12,
                    transparent: true,
                    opacity: 0.96
                });
                const globeMesh = new THREE.Mesh(globeGeo, globeMat);
                globeGroup.add(globeMesh);

                // --- 5. Atmospheric Outer Glow Sphere ---
                const atmoGeo = new THREE.SphereGeometry(globeRadius * 1.035, 64, 64);
                const atmoMat = new THREE.MeshBasicMaterial({
                    color: 0x38bdf8,
                    transparent: true,
                    opacity: 0.12,
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

                    // Beacon Dot
                    const dotGeo = new THREE.SphereGeometry(1.4, 16, 16);
                    const dotMat = new THREE.MeshBasicMaterial({ color: 0x38bdf8 });
                    const dotMesh = new THREE.Mesh(dotGeo, dotMat);
                    dotMesh.position.copy(pos);
                    globeGroup.add(dotMesh);

                    // Outward Beacon Cylinder
                    const cylGeo = new THREE.CylinderGeometry(0.3, 0.3, 4, 8);
                    const cylMat = new THREE.MeshBasicMaterial({ color: 0x06b6d4 });
                    const cylMesh = new THREE.Mesh(cylGeo, cylMat);
                    cylMesh.position.copy(pos.clone().multiplyScalar(1.02));
                    cylMesh.quaternion.setFromUnitVectors(new THREE.Vector3(0, 1, 0), pos.clone().normalize());
                    globeGroup.add(cylMesh);

                    // Pulsing Radar Ring
                    const ringGeo = new THREE.RingGeometry(1.2, 2.2, 32);
                    const ringMat = new THREE.MeshBasicMaterial({
                        color: 0x38bdf8,
                        side: THREE.DoubleSide,
                        transparent: true,
                        opacity: 0.8
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

                    // Calculate elevated midpoint for 3D curved arc
                    const midVec = originVec.clone().add(destVec).multiplyScalar(0.5);
                    const arcHeight = globeRadius + Math.max(14, Math.min(55, distance * 0.28));
                    const ctrlVec = midVec.clone().normalize().multiplyScalar(arcHeight);

                    const curve = new THREE.QuadraticBezierCurve3(originVec, ctrlVec, destVec);
                    arcCurves.push({ resi: route.resi, curve: curve, origin: route.origin, destination: route.destination });

                    // Arc color by status
                    let arcColor = 0x06b6d4; // Cyan for IN_TRANSIT
                    if (route.status === 'EXCEPTION') arcColor = 0xf43f5e; // Rose for EXCEPTION
                    else if (route.status === 'DELIVERED') arcColor = 0x10b981; // Emerald for DELIVERED

                    // Arc Line Mesh
                    const curvePoints = curve.getPoints(60);
                    const lineGeo = new THREE.BufferGeometry().setFromPoints(curvePoints);
                    const lineMat = new THREE.LineBasicMaterial({
                        color: arcColor,
                        transparent: true,
                        opacity: 0.85,
                        linewidth: 2
                    });
                    const lineMesh = new THREE.Line(lineGeo, lineMat);
                    globeGroup.add(lineMesh);

                    // Traveling pulse light packet
                    const pGeo = new THREE.SphereGeometry(1.6, 12, 12);
                    const pMat = new THREE.MeshBasicMaterial({
                        color: arcColor,
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

                // Initial orientation: Center on Southeast Asia & Indian Ocean (Singapore / Indonesia trade corridors)
                globeGroup.rotation.y = -1.2;
                globeGroup.rotation.x = 0.25;

                // --- 8. Smooth Mouse & Touch Interactivity ---
                let isDragging = false;
                let prevMousePos = { x: 0, y: 0 };
                let autoRotate = true;
                let targetRotationY = globeGroup.rotation.y;
                let targetRotationX = globeGroup.rotation.x;
                let rotateTimeout = null;

                const btnToggle = document.getElementById('btn-toggle-rotate');
                const rotateDot = document.getElementById('rotate-dot');
                const rotateText = document.getElementById('rotate-text');

                function updateRotateState(state) {
                    autoRotate = state;
                    if (rotateDot && rotateText) {
                        if (autoRotate) {
                            rotateDot.className = "w-2 h-2 rounded-full bg-cyan-400 animate-pulse";
                            rotateText.textContent = "Rotate: ON";
                        } else {
                            rotateDot.className = "w-2 h-2 rounded-full bg-slate-500";
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
                        // Auto-resume rotation after 4s idle
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
                    // Center roughly on midpoint between origin and destination
                    const midLng = (match.origin.lng + match.destination.lng) / 2;
                    const midLat = (match.origin.lat + match.destination.lat) / 2;

                    // Convert lat/lng to target rotation angles
                    const targetY = -((midLng + 90) * (Math.PI / 180));
                    const targetX = (midLat * 0.4) * (Math.PI / 180);

                    globeGroup.rotation.y = targetY;
                    globeGroup.rotation.x = targetX;
                    camera.position.z = 240;

                    // Re-enable rotation after 6s
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
                            r.mesh.material.opacity = 0.8;
                        } else {
                            r.mesh.scale.set(r.scale, r.scale, r.scale);
                            r.mesh.material.opacity = Math.max(0, 0.8 * (1 - (r.scale - 1) / 1.6));
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
                    ctx.fillStyle = '#070b14';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    const cx = canvas.width / 2;
                    const cy = canvas.height / 2;
                    const maxR = Math.min(cx, cy) * 0.85;

                    ctx.strokeStyle = '#0284c7';
                    ctx.lineWidth = 1;
                    for (let r = 0.25; r <= 1; r += 0.25) {
                        ctx.beginPath();
                        ctx.arc(cx, cy, maxR * r, 0, Math.PI * 2);
                        ctx.stroke();
                    }
                    ctx.fillStyle = '#38bdf8';
                    ctx.font = '14px monospace';
                    ctx.textAlign = 'center';
                    ctx.fillText('CargoTrace Radar Telemetry Active', cx, cy - 20);
                }
                drawRadar();
            }
        </script>
    @endif

</body>
</html>