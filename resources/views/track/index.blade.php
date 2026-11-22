<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CargoTrace - Global Shipment Visibility & Real-Time Tracking</title>
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
        
        /* Custom scrollbar for frosted drawer */
        .hud-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .hud-scroll::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.4); }
        .hud-scroll::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 4px; }
        .hud-scroll::-webkit-scrollbar-thumb:hover { background: #64748b; }
    </style>
</head>
<body class="bg-[#eaf1f6] font-sans text-slate-800 min-h-screen flex flex-col selection:bg-slate-800 selection:text-white">

    <!-- Navbar: Frosted Ice Glassmorphism -->
    <nav class="bg-[#eaf1f6]/75 backdrop-blur-md border-b border-white/60 sticky top-0 z-50 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('track.index') }}" class="text-2xl font-black text-[#0b1723] tracking-tight flex items-center gap-2.5 group">
                    <span class="p-1.5 rounded-xl bg-[#0c1a24] text-white shadow-md group-hover:scale-105 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </span>
                    <span>CargoTrace<span class="text-[#2c566e]">.</span></span>
                </a>
                <span class="hidden md:inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-bold rounded-full bg-[#c8dde8] text-[#1e3c4f] border border-white/70 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-[#2a6d85] animate-ping"></span>
                    LIVE 3D Telemetry
                </span>
            </div>
            
            <div class="flex items-center gap-4 sm:gap-6">
                <a href="#active-routes" class="hidden sm:inline-block text-xs font-bold text-slate-700 hover:text-[#0b1723] transition">
                    Trade Corridors
                </a>
                <a href="#platform-specs" class="hidden sm:inline-block text-xs font-bold text-slate-700 hover:text-[#0b1723] transition">
                    Specs & Architecture
                </a>
                <a href="{{ route('api.docs') }}" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-bold text-[#1e3c4f] hover:text-[#0b1723] bg-white/70 hover:bg-white px-3 py-1.5 rounded-full border border-white/90 shadow-sm transition">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>REST API Docs</span>
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
                    <div class="flex items-center gap-3">
                        <span class="hidden lg:inline-block text-xs font-mono font-bold text-slate-500 uppercase">
                            | {{ $userRole }}
                        </span>
                        <a href="{{ $dashRoute }}" class="text-xs font-bold text-white bg-[#0c1a24] hover:bg-slate-800 shadow-md px-5 py-2.5 rounded-full transition flex items-center gap-1.5">
                            <span>Dashboard</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-bold text-white bg-[#0c1a24] hover:bg-slate-800 shadow-md px-6 py-2.5 rounded-full transition">
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
            <!-- LIQUID ICE & SILVER-SLATE 3D GLOBE HERO EXPERIENCE       -->
            <!-- ======================================================== -->
            <section class="relative min-h-[90vh] lg:min-h-[92vh] flex flex-col justify-center overflow-hidden bg-[#e8edf1] border-b border-white/60">
                
                <!-- Authentic Frosted Ice & Slate Swirl Background Image (from Design Mockup) -->
                <div class="absolute inset-0 pointer-events-none select-none z-0 overflow-hidden">
                    <img src="{{ asset('images/landing_hero_bg.jpg') }}" alt="CargoTrace Hero Background" class="w-full h-full object-cover object-center" />
                </div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center flex-grow">
                    
                    <!-- Left Hero Content & Search Box (5 cols) -->
                    <div class="lg:col-span-5 flex flex-col justify-center space-y-6">
                        
                        <!-- Telemetry Status Tag (Frosted Ice Pill) -->
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/60 border border-white/80 text-xs font-semibold text-[#1e3c4f] w-fit backdrop-blur-md shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-[#2a6d85]"></span>
                            <span class="font-mono text-[11px] uppercase tracking-wider font-bold">REAL-TIME GLOBAL TELEMETRY • 8 Trade Lanes</span>
                        </div>

                        <!-- Main Bold Headline (Exact Typography from Mockup) -->
                        <div>
                            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-[#0b1723] tracking-tight uppercase leading-[1.05]">
                                GLOBAL<br>VISIBILITY.
                            </h1>
                            <p class="mt-4 text-slate-600 text-sm sm:text-base font-medium leading-relaxed max-w-lg">
                                Real-time multimodal cargo visibility across intercontinental air, ocean, and ground freight. Track live coordinates, weather checkpoints, and automated ETA delay analytics.
                            </p>
                        </div>

                        <!-- Tracking Search Box (Frosted Glassmorphism Card) -->
                        <div class="bg-white/80 backdrop-blur-xl p-2.5 rounded-3xl shadow-[0_12px_35px_rgba(42,75,94,0.08)] border border-white/90 text-slate-800">
                            <form action="{{ route('track.search') }}" method="POST" class="flex flex-col sm:flex-row gap-2 items-center">
                                @csrf
                                <div class="relative flex-grow w-full">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                    <input type="text" name="tracking_number" id="tracking_input" value="{{ old('tracking_number') }}" 
                                        placeholder="ENTER RESI : CT-20261118-SEAEUR" required 
                                        class="w-full pl-11 pr-4 py-3 rounded-2xl bg-transparent text-[#0b1723] placeholder-slate-400 focus:outline-none transition font-mono font-bold tracking-wider uppercase text-sm">
                                </div>
                                <button type="submit" class="w-full sm:w-auto py-3 px-6 bg-white hover:bg-slate-50 text-[#0b1723] border border-slate-200/90 font-bold rounded-2xl shadow-sm transition text-xs flex items-center justify-center gap-1.5 whitespace-nowrap">
                                    <span>Track Cargo</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </button>
                            </form>

                            @if(session('error'))
                                <div class="mt-2.5 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-semibold flex items-center gap-2">
                                    <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span>{{ session('error') }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Quick-Click Sample Shipments (Frosted Glass Pills) -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs font-mono font-bold text-slate-500 uppercase tracking-wider">
                                <span class="text-slate-400 lowercase font-sans font-medium text-[11px]">Click to trace</span>
                            </div>
                            <div class="flex flex-wrap gap-2.5">
                                <button type="button" onclick="quickFillTrack('CT-20261118-SEAEUR')" 
                                    class="group text-left px-3.5 py-2.5 rounded-2xl bg-white/75 hover:bg-white text-slate-800 border border-white/90 shadow-sm backdrop-blur transition hover:scale-[1.02]">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-black bg-blue-100 text-blue-800 border border-blue-200">GROUP A</span>
                                        <span class="text-[11px] font-mono font-bold text-[#1e3c4f]">CT-20261118-SEAEUR</span>
                                    </div>
                                    <span class="block text-[10px] text-slate-500 font-medium">SE Asia ➔ Europe • Customs Hold (12h)</span>
                                </button>
                                
                                <button type="button" onclick="quickFillTrack('CT-20261118-ASIANAM')" 
                                    class="group text-left px-3.5 py-2.5 rounded-2xl bg-white/75 hover:bg-white text-slate-800 border border-white/90 shadow-sm backdrop-blur transition hover:scale-[1.02]">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-black bg-teal-100 text-teal-800 border border-teal-200">GROUP B</span>
                                        <span class="text-[11px] font-mono font-bold text-rose-600">CT-20261118-ASIANAM</span>
                                    </div>
                                    <span class="block text-[10px] text-slate-500 font-medium">Asia ➔ North America • Weather Delay (18h)</span>
                                </button>
                                
                                <button type="button" onclick="quickFillTrack('CT-20261118-SEAAUS')" 
                                    class="group text-left px-3.5 py-2.5 rounded-2xl bg-white/75 hover:bg-white text-slate-800 border border-white/90 shadow-sm backdrop-blur transition hover:scale-[1.02]">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-black bg-amber-100 text-amber-800 border border-amber-200">GROUP C</span>
                                        <span class="text-[11px] font-mono font-bold text-emerald-700">CT-20261118-SEAAUS</span>
                                    </div>
                                    <span class="block text-[10px] text-slate-500 font-medium">SE Asia ➔ Australia • Doc Issue (24h)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Live Telemetry KPI Counters (Frosted White Cards) -->
                        <div class="grid grid-cols-4 gap-2.5 pt-2">
                            <div class="p-3 rounded-2xl bg-white/75 backdrop-blur-md border border-white/90 text-center shadow-[0_6px_20px_rgba(42,75,94,0.06)]">
                                <div class="text-xl font-black font-mono text-[#0b1723]">{{ $stats['total'] }}</div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">TOTAL</div>
                            </div>
                            <div class="p-3 rounded-2xl bg-white/75 backdrop-blur-md border border-white/90 text-center shadow-[0_6px_20px_rgba(42,75,94,0.06)]">
                                <div class="text-xl font-black font-mono text-[#1e3c4f]">{{ $stats['in_transit'] }}</div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">TRANSIT</div>
                            </div>
                            <div class="p-3 rounded-2xl bg-white/75 backdrop-blur-md border border-white/90 text-center shadow-[0_6px_20px_rgba(42,75,94,0.06)]">
                                <div class="text-xl font-black font-mono text-rose-600">{{ $stats['exceptions'] }}</div>
                                <div class="text-[10px] font-bold text-rose-700 uppercase tracking-tight">DELAYS</div>
                            </div>
                            <div class="p-3 rounded-2xl bg-white/75 backdrop-blur-md border border-white/90 text-center shadow-[0_6px_20px_rgba(42,75,94,0.06)]">
                                <div class="text-xl font-black font-mono text-emerald-700">{{ $stats['delivered'] }}</div>
                                <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-tight">ARRIVED</div>
                            </div>
                        </div>

                    </div>

                    <!-- Right 3D Interactive WebGL Globe Stage (7 cols) -->
                    <div class="lg:col-span-7 relative h-[460px] sm:h-[540px] lg:h-[600px] w-full flex items-center justify-center">
                        
                        <!-- 3D Canvas Container -->
                        <div id="globe-container" class="w-full h-full relative cursor-grab active:cursor-grabbing select-none flex items-center justify-center">
                            <canvas id="globe-canvas" class="w-full h-full block"></canvas>
                        </div>

                        <!-- 3D Globe HUD Overlay: Top Right Controls (Frosted Glass) -->
                        <div class="absolute top-4 right-4 z-20 flex flex-col gap-2">
                            <button id="btn-toggle-rotate" type="button" title="Toggle Auto-Rotation"
                                class="px-3.5 py-1.5 bg-white/65 hover:bg-white text-slate-800 border border-white/80 rounded-xl text-xs font-mono font-bold backdrop-blur shadow-sm transition flex items-center gap-1.5">
                                <span id="rotate-dot" class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span id="rotate-text">Rotate : ON</span>
                            </button>
                            <div class="flex gap-1 justify-end">
                                <button id="btn-zoom-in" type="button" title="Zoom In"
                                    class="w-8 h-8 bg-white/65 hover:bg-white text-slate-800 border border-white/80 rounded-xl text-base font-bold backdrop-blur shadow-sm flex items-center justify-center transition">
                                    +
                                </button>
                                <button id="btn-zoom-out" type="button" title="Zoom Out"
                                    class="w-8 h-8 bg-white/65 hover:bg-white text-slate-800 border border-white/80 rounded-xl text-base font-bold backdrop-blur shadow-sm flex items-center justify-center transition">
                                    −
                                </button>
                                <button id="btn-reset-view" type="button" title="Reset View"
                                    class="px-2.5 h-8 bg-white/65 hover:bg-white text-slate-800 border border-white/80 rounded-xl text-xs font-mono font-bold backdrop-blur shadow-sm flex items-center justify-center transition">
                                    Reset
                                </button>
                            </div>
                        </div>

                        <!-- Arc Status Legend: Bottom Center (Frosted Glass matching Mockup) -->
                        <div class="absolute bottom-6 left-6 z-20 bg-white/60 backdrop-blur-md border border-white/80 px-4 py-2 rounded-2xl shadow-sm flex items-center gap-4 text-xs font-mono font-bold text-slate-700">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-white shadow-sm ring-1 ring-slate-300"></span>
                                <span>In Transit</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#3d657d] shadow-sm"></span>
                                <span>Exception</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#10b981] shadow-sm"></span>
                                <span>Delivered</span>
                            </div>
                        </div>

                        <!-- Active Route Drawer: Bottom Right (Frosted Glass matching Mockup) -->
                        <div class="absolute bottom-6 right-4 z-20 max-w-xs hidden sm:block">
                            <div class="bg-white/40 backdrop-blur-xl border border-white/60 rounded-2xl p-4 shadow-xl text-slate-800">
                                <div class="flex items-center justify-between pb-2 mb-2 border-b border-white/40 text-[10px] font-mono font-bold text-slate-600 uppercase tracking-wider">
                                    <span>ACTIVE GLOBAL HUBS</span>
                                    <span class="text-[#1e3c4f] font-black">10 PORTS</span>
                                </div>
                                <div class="space-y-2 max-h-28 overflow-y-auto hud-scroll pr-1">
                                    @foreach(collect($globeRoutes)->take(4) as $rt)
                                        <div onclick="focusRoute('{{ $rt['resi'] }}')" class="flex items-center justify-between text-[11px] p-1.5 rounded-xl hover:bg-white/50 cursor-pointer transition">
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full bg-white ring-1 ring-slate-400"></span>
                                                <span class="font-mono font-bold text-slate-800">{{ $rt['origin']['code'] }} ➔ {{ $rt['destination']['code'] }}</span>
                                            </div>
                                            <span class="text-[10px] text-[#2c566e] font-mono font-bold hover:underline">Focus</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </section>

            <!-- ======================================================== -->
            <!-- ACTIVE TRADE CORRIDORS GRID (Frosted Ice Cards)          -->
            <!-- ======================================================== -->
            <section id="active-routes" class="py-16 bg-[#e4eff6]/80 border-b border-white/60">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
                        <div>
                            <span class="text-xs font-mono font-bold uppercase tracking-widest text-[#2c566e]">Intercontinental Corridors</span>
                            <h2 class="text-3xl sm:text-4xl font-black text-[#0b1723] mt-1">Monitored Global Shipments</h2>
                        </div>
                        <div class="mt-4 md:mt-0 flex items-center gap-2 text-xs font-mono font-bold text-[#1e3c4f] bg-white/80 px-3.5 py-1.5 rounded-2xl border border-white/90 shadow-sm">
                            <span>Showing {{ count($allShipments) }} Active Pipelines</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($allShipments as $s)
                            @php
                                $statusBadgeClass = match($s->status) {
                                    'DELIVERED' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'EXCEPTION' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'IN_TRANSIT' => 'bg-sky-50 text-[#1e3c4f] border-sky-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <div class="bg-white/85 backdrop-blur-md rounded-2xl border border-white/90 p-6 flex flex-col justify-between hover:shadow-xl hover:border-slate-300 transition duration-300 shadow-[0_6px_25px_rgba(42,75,94,0.06)] group">
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="font-mono text-xs font-bold text-slate-700 group-hover:text-[#2c566e] transition">{{ $s->shipment_number }}</span>
                                        <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold rounded-full border uppercase {{ $statusBadgeClass }}">
                                            {{ $s->status }}
                                        </span>
                                    </div>

                                    <!-- Hub to Hub Direction -->
                                    <div class="my-4 p-3.5 bg-[#f0f6fa] rounded-xl border border-sky-100/80">
                                        <div class="flex items-center justify-between text-xs">
                                            <div class="text-left">
                                                <div class="text-[10px] text-slate-400 uppercase font-mono font-semibold">Origin</div>
                                                <div class="font-black text-slate-900">{{ $s->origin?->city ?? 'Origin' }}</div>
                                                <div class="text-[10px] text-[#2c566e] font-mono font-bold">{{ $s->origin?->code }}</div>
                                            </div>
                                            <div class="px-3 flex flex-col items-center">
                                                <svg class="w-5 h-5 text-[#2c566e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-[10px] text-slate-400 uppercase font-mono font-semibold">Destination</div>
                                                <div class="font-black text-slate-900">{{ $s->destination?->city ?? 'Destination' }}</div>
                                                <div class="text-[10px] text-[#2c566e] font-mono font-bold">{{ $s->destination?->code }}</div>
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

                                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                                    <form action="{{ route('track.search') }}" method="POST" class="w-full">
                                        @csrf
                                        <input type="hidden" name="tracking_number" value="{{ $s->shipment_number }}">
                                        <button type="submit" class="w-full py-2.5 px-4 bg-[#132836] hover:bg-[#0c1a24] text-white font-bold rounded-xl text-xs transition duration-200 flex items-center justify-center gap-2 shadow-sm">
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
            <section id="platform-specs" class="py-16 bg-white text-slate-800 border-b border-slate-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    <div class="text-center max-w-2xl mx-auto mb-12">
                        <span class="text-xs font-mono font-bold uppercase tracking-widest text-[#2c566e]">Architecture Overview</span>
                        <h2 class="text-3xl font-black text-slate-900 mt-1">Engineered for Global Logistics</h2>
                        <p class="text-sm text-slate-600 mt-2">Comprehensive stack built with Laravel 12, SQLite, Three.js WebGL, Leaflet OSM, Open-Meteo REST API, and RBAC security.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <div class="p-6 rounded-2xl bg-[#f0f6fa] border border-sky-100/80 space-y-3 shadow-sm hover:border-slate-300 transition">
                            <div class="w-12 h-12 rounded-2xl bg-[#132836] text-white flex items-center justify-center shadow-md">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Deterministic Delay Engine</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Automatically categorizes transit deviations into LOW (+6h), MEDIUM (+18h), and HIGH (+48h) severity, dynamically updating expected arrival times.
                            </p>
                        </div>

                        <div class="p-6 rounded-2xl bg-[#f0f6fa] border border-sky-100/80 space-y-3 shadow-sm hover:border-slate-300 transition">
                            <div class="w-12 h-12 rounded-2xl bg-[#284858] text-white flex items-center justify-center shadow-md">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Live Open-Meteo Satellite Data</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Real-time atmospheric conditions, wind speed, and meteorological telemetry queried at active ocean coordinates without requiring API keys.
                            </p>
                        </div>

                        <div class="p-6 rounded-2xl bg-[#f0f6fa] border border-sky-100/80 space-y-3 shadow-sm hover:border-slate-300 transition">
                            <div class="w-12 h-12 rounded-2xl bg-[#3c657a] text-white flex items-center justify-center shadow-md">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Role-Based Access Control</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Strict role separation across Admin, Logistics Operator, and Customer portals with audit trail logging and printable airway bills (Resi).
                            </p>
                        </div>

                    </div>

                    <!-- UAS Final Project: Skenario Unik Per Kelompok (Slide 23) -->
                    <div class="mt-14 pt-10 border-t border-slate-200/70">
                        <div class="mb-6">
                            <span class="text-xs font-mono font-bold uppercase tracking-widest text-[#2c566e]">UAS • Final Project Specification</span>
                            <h3 class="text-2xl font-black text-slate-900 mt-1">Skenario Unik Per Kelompok (Slide 23)</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Konfigurasi skenario yang dibagikan secara terpisah agar hasil pengujian logistik tidak identik.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Group A Card -->
                            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 relative overflow-hidden flex flex-col justify-between">
                                <div class="absolute left-0 top-0 bottom-0 w-2 bg-[#2563eb]"></div>
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-lg font-black text-slate-900">Group A</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200">ACTIVE</span>
                                    </div>
                                    <ul class="space-y-2 text-xs text-slate-700 font-medium mb-6">
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#2563eb] mt-1.5 flex-shrink-0"></span>
                                            <span><strong>Route:</strong> Southeast Asia → Europe</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#2563eb] mt-1.5 flex-shrink-0"></span>
                                            <span><strong>Delay threshold:</strong> 12 jam</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#2563eb] mt-1.5 flex-shrink-0"></span>
                                            <span><strong>Exception utama:</strong> customs hold</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#2563eb] mt-1.5 flex-shrink-0"></span>
                                            <span><strong>KPI:</strong> on-time delivery rate</span>
                                        </li>
                                    </ul>
                                </div>
                                <form action="{{ route('track.search') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="tracking_number" value="CT-20261118-SEAEUR">
                                    <button type="submit" class="w-full py-2.5 px-4 bg-blue-50 hover:bg-blue-100 text-blue-800 font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5 font-mono">
                                        <span>Lacak CT-20261118-SEAEUR</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    </button>
                                </form>
                            </div>

                            <!-- Group B Card -->
                            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 relative overflow-hidden flex flex-col justify-between">
                                <div class="absolute left-0 top-0 bottom-0 w-2 bg-[#0d9488]"></div>
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-lg font-black text-slate-900">Group B</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-teal-50 text-teal-700 border border-teal-200">ACTIVE</span>
                                    </div>
                                    <ul class="space-y-2 text-xs text-slate-700 font-medium mb-6">
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#0d9488] mt-1.5 flex-shrink-0"></span>
                                            <span><strong>Route:</strong> Asia → North America</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#0d9488] mt-1.5 flex-shrink-0"></span>
                                            <span><strong>Delay threshold:</strong> 18 jam</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#0d9488] mt-1.5 flex-shrink-0"></span>
                                            <span><strong>Exception utama:</strong> weather delay</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#0d9488] mt-1.5 flex-shrink-0"></span>
                                            <span><strong>KPI:</strong> average delay duration</span>
                                        </li>
                                    </ul>
                                </div>
                                <form action="{{ route('track.search') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="tracking_number" value="CT-20261118-ASIANAM">
                                    <button type="submit" class="w-full py-2.5 px-4 bg-teal-50 hover:bg-teal-100 text-teal-800 font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5 font-mono">
                                        <span>Lacak CT-20261118-ASIANAM</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    </button>
                                </form>
                            </div>

                            <!-- Group C Card -->
                            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 relative overflow-hidden flex flex-col justify-between">
                                <div class="absolute left-0 top-0 bottom-0 w-2 bg-[#ea580c]"></div>
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-lg font-black text-slate-900">Group C</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-50 text-amber-800 border border-amber-200">RESOLVED</span>
                                    </div>
                                    <ul class="space-y-2 text-xs text-slate-700 font-medium mb-6">
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#ea580c] mt-1.5 flex-shrink-0"></span>
                                            <span><strong>Route:</strong> Southeast Asia → Australia</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#ea580c] mt-1.5 flex-shrink-0"></span>
                                            <span><strong>Delay threshold:</strong> 24 jam</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#ea580c] mt-1.5 flex-shrink-0"></span>
                                            <span><strong>Exception utama:</strong> documentation issue</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#ea580c] mt-1.5 flex-shrink-0"></span>
                                            <span><strong>KPI:</strong> exception resolution time</span>
                                        </li>
                                    </ul>
                                </div>
                                <form action="{{ route('track.search') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="tracking_number" value="CT-20261118-SEAAUS">
                                    <button type="submit" class="w-full py-2.5 px-4 bg-orange-50 hover:bg-orange-100 text-orange-800 font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5 font-mono">
                                        <span>Lacak CT-20261118-SEAAUS</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    </button>
                                </form>
                            </div>
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
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/90 backdrop-blur-md p-6 rounded-3xl shadow-sm border border-white/80">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest">Shipment Resi</span>
                            @php
                                $statusBadge = match($shipment->status) {
                                    'DELIVERED' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'IN_TRANSIT' => 'bg-sky-50 text-[#1e3c4f] border-sky-200',
                                    'EXCEPTION' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-black border uppercase {{ $statusBadge }}">{{ $shipment->status }}</span>
                        </div>
                        <h2 class="text-2xl font-black font-mono text-slate-900 tracking-tight">{{ $shipment->shipment_number }}</h2>
                    </div>

                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <a href="{{ route('track.index') }}" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-800 border border-slate-200/80 font-bold rounded-2xl text-xs transition flex items-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4 text-[#2c566e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            <span>Back to 3D Globe</span>
                        </a>

                        <form action="{{ route('track.search') }}" method="POST" class="flex w-full md:w-auto gap-2">
                            @csrf
                            <input type="text" name="tracking_number" placeholder="Track another ID..." required class="px-4 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-[#2c566e] outline-none text-xs font-mono font-bold uppercase text-slate-800 w-full md:w-60">
                            <button type="submit" class="px-5 py-2.5 bg-[#132836] hover:bg-[#0c1a24] text-white font-bold rounded-2xl text-xs transition shadow-sm">Search</button>
                        </form>
                    </div>
                </div>

                <!-- ETA & Delay Status Alert Box -->
                @if(isset($delayStatus))
                    @if($delayStatus['is_delayed'])
                        <div class="bg-amber-50 border border-amber-200 rounded-3xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                            <div class="flex items-start gap-3.5">
                                <div class="p-2.5 bg-amber-100 text-amber-700 rounded-2xl mt-0.5 border border-amber-200">
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
                            <span class="text-[11px] font-mono font-semibold text-amber-800 bg-white px-3 py-1.5 rounded-xl border border-amber-200 shadow-sm">
                                Expected: {{ \Carbon\Carbon::parse($delayStatus['expected_arrival'])->format('d M Y, H:i') }}
                            </span>
                        </div>
                    @else
                        <div class="bg-emerald-50 border border-emerald-200 rounded-3xl p-4 flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-emerald-100 text-emerald-700 rounded-xl border border-emerald-200">
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
                <div class="bg-white/90 backdrop-blur-md p-6 sm:p-8 rounded-3xl shadow-sm border border-white/80">
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest">Shipment Journey Progress</span>
                        <span class="text-xs font-mono font-bold text-[#1e3c4f]">{{ $shipment->progress_percent }}% Completed</span>
                    </div>

                    <div class="relative w-full max-w-4xl mx-auto my-6">
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden border border-slate-200/60">
                            <div class="h-full bg-gradient-to-r from-[#3c657a] to-[#132836] rounded-full transition-all duration-700" style="width: {{ $shipment->progress_percent }}%;"></div>
                        </div>

                        <div class="flex justify-between items-center text-[11px] font-mono font-bold text-slate-400 mt-3">
                            <span class="{{ $shipment->progress_percent >= 10 ? 'text-[#1e3c4f]' : '' }}">Created</span>
                            <span class="{{ $shipment->progress_percent >= 30 ? 'text-[#1e3c4f]' : '' }}">Booked</span>
                            <span class="{{ $shipment->progress_percent >= 50 ? 'text-[#1e3c4f]' : '' }}">Picked Up</span>
                            <span class="{{ $shipment->progress_percent >= 75 ? 'text-[#1e3c4f]' : '' }}">In Transit</span>
                            <span class="{{ $shipment->progress_percent >= 100 ? 'text-emerald-700' : '' }}">Delivered</span>
                        </div>
                    </div>
                </div>

                <!-- Main Grid: Leaflet Map & Timeline / Weather -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Map Column (2/3 width) -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="p-4 border-b border-slate-100 bg-[#f8fafc] flex justify-between items-center">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">Geographic Routing & Waypoints</h3>
                                    <p class="text-[11px] text-slate-500">Live coordinates plotted on OpenStreetMap.</p>
                                </div>
                                <span class="text-[10px] font-mono bg-slate-100 text-[#1e3c4f] border border-slate-200 font-bold px-2.5 py-1 rounded-xl">Leaflet OSM</span>
                            </div>
                            <div id="map" class="w-full bg-slate-100" style="height: 480px; z-index: 1;"></div>
                        </div>

                        <!-- Chronological Tracking Timeline -->
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                            <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
                                <div>
                                    <h3 class="text-base font-bold text-slate-900">Tracking Event Timeline</h3>
                                    <p class="text-xs text-slate-500">Immutable audit history of all recorded cargo scan events.</p>
                                </div>
                                <span class="text-xs font-mono font-semibold px-2.5 py-1 bg-slate-100 text-[#1e3c4f] rounded-xl">
                                    {{ $shipment->chronologicalEvents->count() }} Milestones
                                </span>
                            </div>

                            <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                                @forelse($shipment->chronologicalEvents->reverse() as $event)
                                    @php
                                        $isEventException = $event->status === 'EXCEPTION';
                                        $dotColor = $isEventException ? 'bg-rose-500 ring-rose-100' : ($event->status === 'DELIVERED' ? 'bg-emerald-500 ring-emerald-100' : 'bg-[#1e3c4f] ring-slate-100');
                                    @endphp
                                    <div class="relative">
                                        <div class="absolute -left-[27px] top-1.5 w-3.5 h-3.5 rounded-full {{ $dotColor }} ring-4 bg-white"></div>
                                        <div class="bg-[#f8fafc] p-4 rounded-2xl border border-slate-100">
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-mono font-black uppercase tracking-wider {{ $isEventException ? 'text-rose-600' : 'text-[#1e3c4f]' }}">
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
                        
                        <!-- Live Weather Card (Slate-Teal Gradient) -->
                        @if(isset($weather) && $weather['success'])
                            <div class="bg-gradient-to-br from-[#284858] to-[#16303d] text-white p-6 rounded-3xl shadow-lg border border-white/20 relative overflow-hidden">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-sky-200">Live Weather Telemetry</span>
                                        <h4 class="text-base font-bold mt-0.5">Current Checkpoint</h4>
                                    </div>
                                    <span class="text-3xl">{{ $weather['icon'] }}</span>
                                </div>
                                <div class="flex items-baseline gap-2 mb-3">
                                    <span class="text-3xl font-black font-mono">{{ $weather['temperature'] }}°C</span>
                                    <span class="text-xs text-sky-200 font-medium">{{ $weather['condition'] }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-[11px] font-mono text-slate-300 pt-3 border-t border-white/20">
                                    <div>Wind: <b class="text-white">{{ $weather['windspeed'] }} km/h</b></div>
                                    <div class="text-right">Source: <b class="text-white">{{ $weather['source'] }}</b></div>
                                </div>
                            </div>
                        @endif

                        <!-- Route Hubs Card -->
                        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 space-y-4">
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
                        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 space-y-3">
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
                                <span class="font-mono font-bold text-[#1e3c4f]">
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
                        html: '<div style="background:#132836; color:#fff; width:22px; height:22px; border-radius:50%; border:3px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.3); display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:bold;">A</div>',
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
                            var cpColor = isExc ? '#f43f5e' : '#3c657a';
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
                        color: '#284858',
                        weight: 4,
                        opacity: 0.9,
                        dashArray: '8, 8'
                    }).addTo(map);

                    map.fitBounds(polyline.getBounds(), { padding: [50, 50] });
                });
            </script>
        @endif
    </main>

    <!-- Footer: Deep Midnight Slate -->
    <footer class="bg-[#0b1622] border-t border-slate-800 py-10 mt-auto text-slate-400">
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
                const height = container.clientHeight || 550;
                
                const camera = new THREE.PerspectiveCamera(45, width / height, 1, 2000);
                camera.position.set(0, 0, 310);

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

                // --- 2. Lighting: Soft Matte Studio Illumination (No Blinding Hotspot) ---
                const ambientLight = new THREE.AmbientLight(0xffffff, 0.92);
                scene.add(ambientLight);

                const keySun = new THREE.DirectionalLight(0xffffff, 0.88);
                keySun.position.set(220, 340, 180);
                scene.add(keySun);

                const fillTeal = new THREE.DirectionalLight(0xc8dde8, 0.45);
                fillTeal.position.set(-220, -120, -180);
                scene.add(fillTeal);

                // --- 3. High-Resolution Cartographic Earth Texture ---
                const globeRadius = 65;
                const textureLoader = new THREE.TextureLoader();
                const globeTexture = textureLoader.load('{{ asset("images/earth_styled.png") }}');

                // Base Globe Sphere Mesh: Matte Porcelain-Metallic (Matches Reference Mockup)
                const globeGeo = new THREE.SphereGeometry(globeRadius, 64, 64);
                const globeMat = new THREE.MeshStandardMaterial({
                    map: globeTexture,
                    roughness: 0.70,
                    metalness: 0.12
                });
                const globeMesh = new THREE.Mesh(globeGeo, globeMat);
                globeGroup.add(globeMesh);

                // --- 4. Atmospheric Outer Glow Sphere (Soft Translucent White) ---
                const atmoGeo = new THREE.SphereGeometry(globeRadius * 1.025, 64, 64);
                const atmoMat = new THREE.MeshBasicMaterial({
                    color: 0xffffff,
                    transparent: true,
                    opacity: 0.15,
                    side: THREE.BackSide,
                    blending: THREE.AdditiveBlending
                });
                globeGroup.add(new THREE.Mesh(atmoGeo, atmoMat));

                // --- 5. Celestial Orbital Rings (Exact Feature from Mockup) ---
                const orbitGroup = new THREE.Group();
                const ringRadii = [globeRadius * 1.26, globeRadius * 1.34, globeRadius * 1.42];
                const ringTilts = [
                    { rx: 0.6, ry: 0.3, rz: 0.4 },
                    { rx: -0.5, ry: 0.7, rz: -0.35 },
                    { rx: 0.2, ry: -0.8, rz: 0.5 }
                ];

                ringTilts.forEach((tilt, idx) => {
                    const rX = ringRadii[idx];
                    const rY = ringRadii[idx] * 0.94;
                    const ringCurve = new THREE.EllipseCurve(0, 0, rX, rY, 0, 2 * Math.PI, false, 0);
                    const ringPoints = ringCurve.getPoints(90);
                    const ringGeo = new THREE.BufferGeometry().setFromPoints(
                        ringPoints.map(p => new THREE.Vector3(p.x, p.y, 0))
                    );
                    const ringMat = new THREE.LineBasicMaterial({
                        color: 0xffffff,
                        transparent: true,
                        opacity: 0.5,
                        linewidth: 1
                    });
                    const ringMesh = new THREE.Line(ringGeo, ringMat);
                    ringMesh.rotation.set(tilt.rx, tilt.ry, tilt.rz);
                    orbitGroup.add(ringMesh);

                    // Glowing Satellite Beacon Nodes on the Ring
                    [0.2, 0.65].forEach(t => {
                        const pt = ringCurve.getPoint(t);
                        const nodeGeo = new THREE.SphereGeometry(1.6, 12, 12);
                        const nodeMat = new THREE.MeshBasicMaterial({ color: 0xffffff });
                        const nodeMesh = new THREE.Mesh(nodeGeo, nodeMat);
                        const nodePos = new THREE.Vector3(pt.x, pt.y, 0).applyEuler(new THREE.Euler(tilt.rx, tilt.ry, tilt.rz));
                        nodeMesh.position.copy(nodePos);
                        orbitGroup.add(nodeMesh);
                    });
                });
                globeGroup.add(orbitGroup);

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

                    // Beacon Dot: Pure White Node
                    const dotGeo = new THREE.SphereGeometry(1.4, 16, 16);
                    const dotMat = new THREE.MeshBasicMaterial({ color: 0xffffff });
                    const dotMesh = new THREE.Mesh(dotGeo, dotMat);
                    dotMesh.position.copy(pos);
                    globeGroup.add(dotMesh);

                    // Pulsing Radar Ring: White Ripple
                    const ringGeo = new THREE.RingGeometry(0.8, 1.8, 32);
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
                    const arcHeight = globeRadius + Math.max(8, Math.min(30, distance * 0.22));
                    const ctrlVec = midVec.clone().normalize().multiplyScalar(arcHeight);

                    const curve = new THREE.QuadraticBezierCurve3(originVec, ctrlVec, destVec);
                    arcCurves.push({ resi: route.resi, curve: curve, origin: route.origin, destination: route.destination });

                    // Arc line color: Glowing Pure White
                    let arcColor = 0xffffff;
                    let pulseColor = 0xffffff;
                    if (route.status === 'EXCEPTION') {
                        arcColor = 0xf43f5e;
                        pulseColor = 0xff6699;
                    } else if (route.status === 'DELIVERED') {
                        arcColor = 0x10b981;
                        pulseColor = 0x6ee7b7;
                    }

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
                globeGroup.rotation.x = 0.2;

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
                            rotateText.textContent = "Rotate : ON";
                        } else {
                            rotateDot.className = "w-2 h-2 rounded-full bg-slate-400";
                            rotateText.textContent = "Rotate : OFF";
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
                    camera.position.z = Math.max(180, Math.min(480, camera.position.z + e.deltaY * 0.25));
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
                        camera.position.z = Math.max(180, camera.position.z - 30);
                    });
                }
                if (btnZoomOut) {
                    btnZoomOut.addEventListener('click', () => {
                        camera.position.z = Math.min(480, camera.position.z + 30);
                    });
                }
                if (btnReset) {
                    btnReset.addEventListener('click', () => {
                        camera.position.set(0, 0, 310);
                        globeGroup.rotation.set(0.2, -1.2, 0);
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
                    camera.position.z = 250;

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
                        globeGroup.rotation.y += 0.0016;
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
                    ctx.fillStyle = '#284858';
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