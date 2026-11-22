@extends('layouts.admin')

@section('title', 'Detail Resi - Admin')
@section('header_title', 'Detail Resi Pengiriman')

@section('content')
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">
                    Resi: <span class="text-indigo-600">{{ $shipment->shipment_number ?? 'Draft' }}</span>
                </h2>
                @php
                    $statusBadge = match($shipment->status) {
                        'DELIVERED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'IN_TRANSIT' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                        'EXCEPTION' => 'bg-rose-100 text-rose-800 border-rose-200',
                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                    };
                @endphp
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black border uppercase {{ $statusBadge }}">
                    {{ $shipment->status }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Registered by {{ $shipment->creator?->name ?? 'System' }} &bull; {{ $shipment->created_at->format('d M Y, H:i') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.shipments.index') }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold transition shadow-sm">
                &larr; Back to List
            </a>
            <a href="{{ route('admin.shipments.print', $shipment) }}" target="_blank" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Resi
            </a>
            <a href="{{ route('admin.shipments.edit', $shipment) }}" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                Edit Shipment
            </a>
        </div>
    </div>

    <!-- Layout Grid: Kiri (Info & Items) | Kanan (Actions & Timeline) -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <!-- KOLOM KIRI: Informasi Utama -->
        <div class="xl:col-span-2 space-y-6">
            
            <!-- Info Box -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-200 bg-slate-50/60 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-800">Shipment Specifications & Route</h3>
                    <span class="text-xs font-bold text-indigo-700">{{ $shipment->progress_percent }}% Progress</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                    <div class="space-y-4">
                        <div>
                            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Customer (Shipper)</span>
                            <span class="text-sm font-bold text-slate-800">{{ $shipment->customer->name ?? '-' }}</span>
                            <span class="block text-xs text-slate-500">{{ $shipment->customer->email ?? '' }}</span>
                        </div>
                        <div>
                            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Logistics Carrier</span>
                            <span class="text-sm font-bold text-slate-800">{{ $shipment->carrier->name ?? '-' }} ({{ $shipment->carrier->code ?? '' }})</span>
                            <span class="block text-xs text-slate-500">{{ $shipment->carrier->contact_info ?? 'Active Partner' }}</span>
                        </div>
                        <div>
                            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Departure & Arrival Target</span>
                            <span class="text-xs text-slate-700 font-semibold block">Depart: {{ $shipment->expected_departure_at ? $shipment->expected_departure_at->format('d M Y H:i') : 'Pending' }}</span>
                            <span class="text-xs text-indigo-700 font-bold block">Target ETA: {{ $shipment->expected_arrival_at ? $shipment->expected_arrival_at->format('d M Y H:i') : 'Pending' }}</span>
                        </div>
                    </div>
                    
                    <div class="space-y-4 md:border-l md:border-slate-200 md:pl-6">
                        <div>
                            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Origin & Destination Hubs</span>
                            <div class="text-xs font-semibold text-slate-800">
                                <span class="text-indigo-600 font-bold">{{ $shipment->origin->name ?? '-' }}</span> ({{ $shipment->origin->city ?? '' }})
                                <br>&darr;<br>
                                <span class="text-emerald-600 font-bold">{{ $shipment->destination->name ?? '-' }}</span> ({{ $shipment->destination->city ?? '' }})
                            </div>
                        </div>
                        <div>
                            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Cargo Specs & Cost</span>
                            <span class="text-sm font-semibold text-slate-800">{{ $shipment->weight_kg }} kg &bull; {{ $shipment->cargo_type }}</span>
                            <div class="text-base font-black text-emerald-700 mt-1">
                                {{ $shipment->formatted_cost }}
                            </div>
                        </div>
                        
                        <div class="pt-3 border-t border-slate-100">
                            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">ETA Telemetry Analysis</span>
                            @if(isset($delayStatus) && $delayStatus['is_delayed'])
                                <div class="p-2.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-bold">
                                    <div class="flex items-center gap-1.5 mb-0.5">
                                        <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                        <span>Delayed by {{ $delayStatus['delay_hours'] }} Hours</span>
                                        <span class="px-1.5 py-0.2 rounded bg-rose-200 text-rose-900 text-[10px]">{{ $delayStatus['severity'] }}</span>
                                    </div>
                                    <p class="text-[11px] font-normal text-rose-700">{{ $delayStatus['message'] }}</p>
                                </div>
                            @else
                                <div class="p-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                    <span>{{ $delayStatus['message'] ?? 'On Schedule' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Exceptions & Resolution Queue -->
            @if($shipment->exceptions->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 bg-rose-50/40 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                            <h3 class="text-sm font-bold text-rose-900">Recorded Exceptions & Incident Log</h3>
                        </div>
                        <span class="text-xs font-bold text-rose-700">{{ $shipment->exceptions->count() }} Records</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($shipment->exceptions as $exc)
                            <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-black text-slate-800">{{ str_replace('_', ' ', $exc->type) }}</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $exc->severity === 'HIGH' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">{{ $exc->severity }}</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $exc->status === 'RESOLVED' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $exc->status }}</span>
                                    </div>
                                    <p class="text-xs text-slate-600">{{ $exc->description }}</p>
                                    <span class="text-[10px] text-slate-400 mt-1 block">Detected: {{ $exc->detected_at ? $exc->detected_at->format('d M Y H:i') : $exc->created_at->format('d M Y H:i') }}</span>
                                </div>
                                @if($exc->status !== 'RESOLVED')
                                    <form action="{{ route('exceptions.resolve', $exc->id) }}" method="POST" class="flex-shrink-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm">
                                            Resolve Incident
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Items Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-200 bg-slate-50/60">
                    <h3 class="text-sm font-bold text-slate-800">Shipment Items & Manifest</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-200">
                            <tr>
                                <th class="p-3.5">Cargo Description</th>
                                <th class="p-3.5">Category</th>
                                <th class="p-3.5">Quantity</th>
                                <th class="p-3.5">Gross Weight</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($shipment->items as $item)
                                <tr class="hover:bg-slate-50/60">
                                    <td class="p-3.5 font-bold text-slate-800">{{ $item->description ?? 'Standard Cargo' }}</td>
                                    <td class="p-3.5">{{ $item->cargo_type ?? $shipment->cargo_type }}</td>
                                    <td class="p-3.5">{{ $item->quantity ?? 1 }} units</td>
                                    <td class="p-3.5 font-bold">{{ $item->weight_kg ?? $item->weight ?? $shipment->weight_kg }} kg</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-6 text-center text-slate-400 italic">No specific manifest items attached.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN: Operational Actions & Timeline -->
        <div class="xl:col-span-1 space-y-6">
            
            <!-- Action Form 1: Add Tracking Event & Transition Status -->
            @if(!in_array(strtoupper($shipment->status), ['DELIVERED', 'CANCELLED']))
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Log Tracking Event & Advance Status
                    </h3>
                    <form action="{{ route('tracking.store', $shipment->id) }}" method="POST" class="space-y-3.5">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">New Status</label>
                            <select name="status" class="w-full rounded-xl border-slate-300 focus:border-indigo-600 text-xs font-bold" required>
                                <option value="BOOKED" {{ $shipment->status === 'CREATED' ? 'selected' : '' }}>BOOKED (Carrier Confirmed)</option>
                                <option value="PICKED_UP" {{ $shipment->status === 'BOOKED' ? 'selected' : '' }}>PICKED_UP (Origin Pickup)</option>
                                <option value="IN_TRANSIT" {{ in_array($shipment->status, ['PICKED_UP', 'IN_TRANSIT', 'EXCEPTION']) ? 'selected' : '' }}>IN_TRANSIT (On Route / Checkpoint)</option>
                                <option value="DELIVERED">DELIVERED (Final Destination)</option>
                                <option value="CANCELLED">CANCELLED (Order Void)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Location Checkpoint</label>
                            <select name="location_id" class="w-full rounded-xl border-slate-300 focus:border-indigo-600 text-xs">
                                <option value="">-- Optional / Current Location --</option>
                                @foreach(\App\Models\Location::orderBy('name')->get() as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Event Note</label>
                            <input type="text" name="description" placeholder="e.g. Cleared transit checkpoint, loading cargo" required class="w-full rounded-xl border-slate-300 focus:border-indigo-600 text-xs">
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            Register Milestone Event
                        </button>
                    </form>
                </div>

                <!-- Action Form 2: Report Exception -->
                <div class="bg-rose-50/60 p-5 rounded-2xl border border-rose-200 shadow-sm">
                    <h3 class="text-sm font-bold text-rose-900 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Report Shipment Exception
                    </h3>
                    <form action="{{ route('exceptions.store', $shipment->id) }}" method="POST" class="space-y-3.5">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Exception Category</label>
                            <select name="type" class="w-full rounded-xl border-slate-300 focus:border-rose-600 text-xs font-semibold" required>
                                <option value="CUSTOMS_HOLD">CUSTOMS_HOLD (Customs Inspection Hold)</option>
                                <option value="WEATHER_DELAY">WEATHER_DELAY (Severe Storm / Sea State)</option>
                                <option value="PORT_CONGESTION">PORT_CONGESTION (Berth Congestion Delay)</option>
                                <option value="DAMAGED">DAMAGED (Cargo Damaged in Transit)</option>
                                <option value="MISROUTED">MISROUTED (Misdirected Container)</option>
                                <option value="DOCUMENTATION_ISSUE">DOCUMENTATION_ISSUE (Missing BOL / Permit)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Severity Level</label>
                            <select name="severity" class="w-full rounded-xl border-slate-300 focus:border-rose-600 text-xs font-semibold" required>
                                <option value="LOW">LOW (Under 6h impact)</option>
                                <option value="MEDIUM" selected>MEDIUM (6-24h delay)</option>
                                <option value="HIGH">HIGH (Major bottleneck / >24h delay)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Incident Details</label>
                            <textarea name="description" rows="2" class="w-full rounded-xl border-slate-300 focus:border-rose-600 text-xs" required placeholder="Describe the operational incident..."></textarea>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            File Incident Report
                        </button>
                    </form>
                </div>
            @endif

            <!-- Chronological Timeline -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h3 class="text-sm font-bold text-slate-800">Timeline & Audit</h3>
                    <span class="text-[11px] text-slate-400 font-semibold">{{ $shipment->trackingEvents->count() }} Events</span>
                </div>
                
                <div class="relative border-l-2 border-slate-200 ml-2 space-y-5 pb-2">
                    @forelse($shipment->trackingEvents as $event)
                        @php
                            $isExc = $event->status === 'EXCEPTION';
                            $dotClass = $isExc ? 'bg-rose-600 ring-rose-100' : ($event->status === 'DELIVERED' ? 'bg-emerald-600 ring-emerald-100' : 'bg-indigo-600 ring-indigo-100');
                        @endphp
                        <div class="relative pl-5">
                            <div class="absolute -left-1.5 top-1 w-3 h-3 rounded-full {{ $dotClass }} ring-4 bg-white"></div>
                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-bold text-xs uppercase tracking-wider {{ $isExc ? 'text-rose-600' : 'text-slate-800' }}">
                                        {{ $event->status }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-semibold">
                                        {{ \Carbon\Carbon::parse($event->event_time)->format('d M, H:i') }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 mb-1 leading-relaxed">{{ $event->description }}</p>
                                @if($event->location)
                                    <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded inline-block">
                                        📍 {{ $event->location->name }} ({{ $event->location->city }})
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="pl-4 text-xs text-slate-400 italic">No events recorded.</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
@endsection
