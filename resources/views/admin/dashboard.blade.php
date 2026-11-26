@extends('layouts.admin')

@section('title', 'Dashboard Administrator')
@section('header_title', 'GoTrace Logistics Control Tower')

@section('content')
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Global Shipment Visibility & Control Tower</h2>
            <p class="text-slate-500 text-sm mt-1">Real-time telemetry, delay thresholds, exception monitoring, and carrier performance metrics.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                System Operational
            </span>
            <a href="{{ route('api.docs') }}" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                <span>REST API Docs</span>
            </a>
            <a href="{{ route('admin.shipments.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create Shipment
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
        <!-- Total Shipments -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 relative overflow-hidden">
            <div class="p-3.5 bg-blue-50 text-blue-600 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Shipments</p>
                <h3 class="text-2xl font-black text-slate-800 mt-0.5">{{ number_format($totalShipments) }}</h3>
                <span class="text-[11px] text-slate-500 font-medium">All recorded orders</span>
            </div>
        </div>

        <!-- In Transit -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 relative overflow-hidden">
            <div class="p-3.5 bg-indigo-50 text-indigo-600 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">In Transit</p>
                <h3 class="text-2xl font-black text-indigo-700 mt-0.5">{{ number_format($inTransit) }}</h3>
                <span class="text-[11px] text-indigo-600 font-medium">Active on routes</span>
            </div>
        </div>

        <!-- Delivered -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 relative overflow-hidden">
            <div class="p-3.5 bg-emerald-50 text-emerald-600 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Delivered</p>
                <h3 class="text-2xl font-black text-emerald-700 mt-0.5">{{ number_format($delivered) }}</h3>
                <span class="text-[11px] text-emerald-600 font-medium">Fulfilled successfully</span>
            </div>
        </div>

        <!-- Delay & Exception Rate -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex justify-between items-center mb-2">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Reliability KPIs</p>
                <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[10px] font-bold rounded border border-amber-200">{{ $delayRate }}% Delay Rate</span>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-1">
                <div class="bg-amber-50/70 p-2.5 rounded-xl border border-amber-100">
                    <p class="text-[10px] font-bold text-amber-700 uppercase">Delayed</p>
                    <p class="text-lg font-black text-amber-800">{{ number_format($delayed) }}</p>
                </div>
                <div class="bg-rose-50/70 p-2.5 rounded-xl border border-rose-100">
                    <p class="text-[10px] font-bold text-rose-700 uppercase">Exceptions</p>
                    <p class="text-lg font-black text-rose-800">{{ number_format($openExceptions) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Telemetry Map Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-8 overflow-hidden">
        <div class="p-5 border-b border-slate-200 bg-slate-50/60 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-bold text-slate-800">Live Global Shipment Telemetry</h3>
                    <span class="flex h-2.5 w-2.5 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-indigo-600"></span>
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Tracking checkpoints for active sea, air, and ground shipments powered by OpenStreetMap.</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-indigo-600 inline-block"></span> Normal / Transit</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-600 inline-block"></span> Exception Hold</span>
            </div>
        </div>
        <div id="dashboardMap" class="w-full bg-slate-100" style="height: 420px; z-index: 1;"></div>
    </div>

    <!-- Analytics Charts Grid: Status & Carrier Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Chart 1: Status Distribution -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Shipment Lifecycle Distribution</h3>
                    <p class="text-xs text-slate-500">Breakdown of orders across pipeline stages</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">Real-time</span>
            </div>
            <div class="relative flex items-center justify-center" style="height: 260px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Carrier Performance Breakdown -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Shipments by Logistics Carrier</h3>
                    <p class="text-xs text-slate-500">Volume routed through partner carrier networks</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">By Volume</span>
            </div>
            <div class="relative flex items-center justify-center" style="height: 260px;">
                <canvas id="carrierChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Open Exceptions Action Queue -->
    @if(isset($recentExceptions) && $recentExceptions->count() > 0)
        <div class="bg-white rounded-2xl border border-rose-200 shadow-sm overflow-hidden mb-8">
            <div class="p-5 border-b border-rose-100 bg-rose-50/40 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-rose-100 text-rose-700 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-rose-900">Priority Exception Action Queue</h3>
                        <p class="text-xs text-rose-700">Immediate operations intervention required for held or delayed shipments.</p>
                    </div>
                </div>
                <a href="{{ route('admin.shipments.index') }}" class="text-xs font-bold text-rose-700 hover:text-rose-900 underline">View all shipments &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">Shipment</th>
                            <th class="p-3.5">Type</th>
                            <th class="p-3.5">Severity</th>
                            <th class="p-3.5">Detected At</th>
                            <th class="p-3.5">Status</th>
                            <th class="p-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentExceptions as $exc)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="p-3.5 font-bold text-indigo-700">
                                    <a href="{{ route('admin.shipments.show', $exc->shipment_id) }}" class="hover:underline">
                                        {{ $exc->shipment?->shipment_number ?? '#' . $exc->shipment_id }}
                                    </a>
                                </td>
                                <td class="p-3.5 font-semibold text-slate-800">
                                    {{ str_replace('_', ' ', $exc->type) }}
                                </td>
                                <td class="p-3.5">
                                    @php
                                        $sevClass = match($exc->severity) {
                                            'HIGH' => 'bg-rose-100 text-rose-700 border-rose-200',
                                            'MEDIUM' => 'bg-amber-100 text-amber-700 border-amber-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $sevClass }}">{{ $exc->severity }}</span>
                                </td>
                                <td class="p-3.5 text-slate-500">{{ \Carbon\Carbon::parse($exc->detected_at)->diffForHumans() }}</td>
                                <td class="p-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">{{ $exc->status }}</span>
                                </td>
                                <td class="p-3.5 text-right">
                                    <a href="{{ route('admin.shipments.show', $exc->shipment_id) }}" class="px-3 py-1 bg-slate-800 hover:bg-slate-900 text-white rounded font-bold text-[11px] transition">
                                        Inspect
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Leaflet & Chart.js CDNs -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Initialise Leaflet Map
            var shipments = @json($activeShipments ?? []);
            var map = L.map('dashboardMap').setView([1.3521, 103.8198], 4);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors | GoTrace'
            }).addTo(map);

            var bounds = [];

            shipments.forEach(function(shipment) {
                if (shipment.lat && shipment.lng) {
                    var isException = shipment.status === 'EXCEPTION';
                    var bgColor = isException ? '#e11d48' : '#4f46e5';

                    var customIcon = L.divIcon({
                        className: 'custom-pin',
                        html: `
                            <div style="display:flex; flex-direction:column; align-items:center;">
                                <div style="background:${bgColor}; color:#fff; font-size:9px; font-weight:bold; padding:2px 6px; border-radius:4px; box-shadow:0 2px 4px rgba(0,0,0,0.2); white-space:nowrap; margin-bottom:2px;">
                                    ${shipment.resi}
                                </div>
                                <div style="width:14px; height:14px; background:${bgColor}; border:2px solid #fff; border-radius:50%; box-shadow:0 0 8px rgba(0,0,0,0.3);"></div>
                            </div>
                        `,
                        iconSize: [80, 40],
                        iconAnchor: [40, 36]
                    });

                    var marker = L.marker([shipment.lat, shipment.lng], { icon: customIcon }).addTo(map);
                    marker.bindPopup(`
                        <div style="font-family:sans-serif; text-align:center; padding:4px;">
                            <b style="color:#4338ca; font-size:12px; display:block; margin-bottom:4px;">${shipment.resi}</b>
                            <span style="font-size:10px; font-weight:bold; padding:2px 6px; border-radius:4px; background:#f1f5f9; text-transform:uppercase;">${shipment.status}</span>
                            <p style="font-size:11px; color:#64748b; margin-top:6px; margin-bottom:2px;">${shipment.lokasi}</p>
                            <p style="font-size:10px; color:#94a3b8;">${shipment.origin} &rarr; ${shipment.destination}</p>
                        </div>
                    `);

                    bounds.push([shipment.lat, shipment.lng]);
                }
            });

            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50], maxZoom: 7 });
            }

            // 2. Initialise Chart.js: Status Distribution (Doughnut)
            var statusData = @json($statusBreakdown ?? []);
            var statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: [
                            '#94a3b8', // CREATED (Slate)
                            '#38bdf8', // BOOKED (Sky)
                            '#818cf8', // PICKED_UP (Indigo)
                            '#4f46e5', // IN_TRANSIT (Primary)
                            '#10b981', // DELIVERED (Emerald)
                            '#f43f5e', // EXCEPTION (Rose)
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } },
                    },
                    cutout: '65%'
                }
            });

            // 3. Initialise Chart.js: Carrier Distribution (Bar)
            var carrierData = @json($carrierBreakdown ?? []);
            var carrierLabels = carrierData.map(function(c) { return c.name; });
            var carrierCounts = carrierData.map(function(c) { return c.count; });

            var carrierCtx = document.getElementById('carrierChart').getContext('2d');
            new Chart(carrierCtx, {
                type: 'bar',
                data: {
                    labels: carrierLabels,
                    datasets: [{
                        label: 'Shipments',
                        data: carrierCounts,
                        backgroundColor: '#6366f1',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } },
                        x: { ticks: { font: { size: 10 } } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    </script>
@endsection
