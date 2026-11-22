<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Operator Logistik - CargoTrace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">
    <!-- Navbar (Tema Teal) -->
    <nav class="bg-teal-700 text-white p-4 shadow-md flex justify-between items-center sticky top-0 z-30">
        <div class="flex items-center gap-3">
            <a href="{{ route('operator.dashboard') }}" class="font-black text-xl tracking-wide flex items-center gap-2">
                CargoTrace<span class="text-teal-300">.</span>
            </a>
            <span class="text-xs font-bold bg-teal-800 text-teal-200 px-2.5 py-1 rounded-lg uppercase tracking-wider">Logistics Operator</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-xs font-semibold text-teal-100 hidden sm:inline">Petugas: {{ auth()->user()->name }}</span>
            <a href="{{ route('track.index') }}" target="_blank" class="text-xs font-bold text-teal-100 hover:text-white underline">
                Portal Pelacakan Publik &nearr;
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
        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight">Panel Operasional Pengiriman (Dispatch)</h1>
                <p class="text-xs md:text-sm text-slate-500 mt-1">Pantau pergerakan armada, catat checkpoint perjalanan, dan tangani kendala kargo secara deterministik.</p>
            </div>
            <a href="{{ route('operator.shipments.create') }}" class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                + Buat Pengiriman Baru
            </a>
        </div>

        <!-- Alert Sukses / Error -->
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

        <!-- Operational KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3.5">
                <div class="p-3 bg-teal-50 text-teal-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Operasional</span>
                    <span class="text-2xl font-black text-slate-800">{{ $totalOperational ?? 0 }}</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3.5">
                <div class="p-3 bg-indigo-50 text-indigo-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">In Transit</span>
                    <span class="text-2xl font-black text-indigo-700">{{ $inTransit ?? 0 }}</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3.5">
                <div class="p-3 bg-amber-50 text-amber-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Terlambat (Delayed)</span>
                    <span class="text-2xl font-black text-amber-700">{{ $delayed ?? 0 }}</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3.5">
                <div class="p-3 bg-rose-50 text-rose-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Open Exceptions</span>
                    <span class="text-2xl font-black text-rose-700">{{ $openExceptions ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <form action="{{ route('operator.dashboard') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Resi / Kargo</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nomor resi atau kargo..." class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Filter Status</label>
                    <select name="status" class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white">
                        <option value="">Semua Status</option>
                        @foreach(['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'EXCEPTION', 'CANCELLED'] as $st)
                            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Filter Keterlambatan</label>
                    <select name="delay_status" class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white">
                        <option value="">Semua Jadwal</option>
                        <option value="delayed" {{ request('delay_status') === 'delayed' ? 'selected' : '' }}>⚠️ Terlambat (Delayed)</option>
                        <option value="on_time" {{ request('delay_status') === 'on_time' ? 'selected' : '' }}>✅ On Time</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm">
                        Filter
                    </button>
                    <a href="{{ route('operator.dashboard') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-2 rounded-xl text-xs font-bold transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Pengiriman -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-200 bg-slate-50/60 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-800">Manifes Pengiriman Logistik Aktif</h3>
                <span class="text-xs text-slate-500 font-semibold">{{ $shipments->total() }} Data Ditemukan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider border-b border-slate-200 font-bold">
                            <th class="px-5 py-3.5">No. Resi & Kargo</th>
                            <th class="px-5 py-3.5">Pelanggan</th>
                            <th class="px-5 py-3.5">Carrier</th>
                            <th class="px-5 py-3.5">Rute (Asal &rarr; Tujuan)</th>
                            <th class="px-5 py-3.5">Target ETA</th>
                            <th class="px-5 py-3.5">Status & Alert</th>
                            <th class="px-5 py-3.5 text-center">Aksi Operasional</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($shipments as $shipment)
                            @php
                                $isDelayed = $shipment->status !== 'DELIVERED' && $shipment->expected_arrival_at && $shipment->expected_arrival_at->isPast();
                                $hasExceptions = $shipment->exceptions->whereIn('status', ['OPEN', 'INVESTIGATING'])->count() > 0;
                                $statusBadge = match($shipment->status) {
                                    'DELIVERED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'IN_TRANSIT' => 'bg-teal-100 text-teal-800 border-teal-200',
                                    'EXCEPTION' => 'bg-rose-100 text-rose-800 border-rose-200 animate-pulse',
                                    'BOOKED' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'PICKED_UP' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('operator.shipments.show', $shipment->shipment_number) }}" class="font-bold text-teal-700 hover:underline">
                                        {{ $shipment->shipment_number }}
                                    </a>
                                    <p class="text-[11px] text-slate-400 capitalize mt-0.5">{{ $shipment->cargo_type }} &bull; {{ number_format($shipment->weight_kg, 0) }} kg</p>
                                </td>
                                <td class="px-5 py-3.5 font-semibold text-slate-800">
                                    {{ $shipment->customer->name ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="font-medium text-slate-800">{{ $shipment->carrier->name ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-400 block uppercase font-bold">{{ $shipment->carrier->code ?? '' }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-1.5 font-bold">
                                        <span class="text-teal-700">{{ $shipment->origin->code ?? '-' }}</span>
                                        <span class="text-slate-400">&rarr;</span>
                                        <span class="text-indigo-700">{{ $shipment->destination->code ?? '-' }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400">{{ $shipment->origin->city ?? '' }} to {{ $shipment->destination->city ?? '' }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($shipment->expected_arrival_at)
                                        <span class="font-semibold text-slate-800 block">{{ $shipment->expected_arrival_at->format('d M Y, H:i') }}</span>
                                        @if($isDelayed)
                                            <span class="text-[10px] font-bold text-rose-600 inline-flex items-center gap-0.5">⚠️ Terlambat</span>
                                        @else
                                            <span class="text-[10px] font-semibold text-emerald-600">On Time</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400 italic">Pending</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase {{ $statusBadge }}">
                                            {{ $shipment->status }}
                                        </span>
                                        @if($hasExceptions)
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-black bg-rose-600 text-white uppercase tracking-wider">
                                                Hold Incident
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('operator.shipments.show', $shipment->shipment_number) }}" class="px-3 py-1.5 bg-teal-50 hover:bg-teal-100 text-teal-700 rounded-lg text-xs font-bold transition border border-teal-200">
                                            Detail, Peta & Event
                                        </a>
                                        <a href="{{ route('operator.shipments.edit', $shipment->shipment_number) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-200">
                                            Status &rarr;
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic">
                                    Belum ada data resi pengiriman yang sesuai dengan kriteria filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($shipments->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $shipments->links() }}
                </div>
            @endif
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 p-4 text-center text-xs text-slate-500 mt-8">
        &copy; {{ date('Y') }} CargoTrace &bull; Enterprise Global Shipment Visibility Platform
    </footer>
</body>
</html>
