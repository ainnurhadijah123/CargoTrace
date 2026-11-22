@extends('layouts.admin')

@section('title', 'Daftar Pengiriman - Admin')
@section('header_title', 'Daftar Pengiriman Kargo & Visibilitas')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Manajemen Resi & Telemetri Pengiriman</h2>
            <p class="text-xs text-slate-500 mt-1">Cari, filter rute, pantau keterlambatan, dan kelola seluruh siklus hidup kargo.</p>
        </div>
        <a href="{{ route('admin.shipments.create') }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Buat Resi Baru
        </a>
    </div>

    <!-- Search & Advanced Filter Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-6">
        <form method="GET" action="{{ route('admin.shipments.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search by Number or Cargo -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Nomor Resi / Kargo</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="cth: CT-20261118..." 
                            class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Pengiriman</label>
                    <select name="status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                        <option value="">Semua Status</option>
                        @foreach(['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'EXCEPTION', 'CANCELLED'] as $st)
                            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Carrier Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Maskapai / Carrier</label>
                    <select name="carrier_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                        <option value="">Semua Carrier</option>
                        @foreach($carriers as $c)
                            <option value="{{ $c->id }}" {{ request('carrier_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Delay Status Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kondisi Keterlambatan</label>
                    <select name="delay_status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                        <option value="">Semua Kondisi</option>
                        <option value="delayed" {{ request('delay_status') === 'delayed' ? 'selected' : '' }}>⚠️ Terlambat (Delayed)</option>
                        <option value="on_time" {{ request('delay_status') === 'on_time' ? 'selected' : '' }}>✅ Sesuai Jadwal (On Time)</option>
                    </select>
                </div>

                <!-- Origin Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Lokasi Asal (Origin)</label>
                    <select name="origin_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                        <option value="">Semua Asal</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('origin_id') == $loc->id ? 'selected' : '' }}>{{ $loc->city }} ({{ $loc->code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Destination Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Lokasi Tujuan (Destination)</label>
                    <select name="destination_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                        <option value="">Semua Tujuan</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('destination_id') == $loc->id ? 'selected' : '' }}>{{ $loc->city }} ({{ $loc->code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range (From - To) -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal Dibuat (Dari)</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal Dibuat (Sampai)</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <!-- Sort and Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-between sm:items-center pt-3 border-t border-slate-100 gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-semibold">Urutkan:</span>
                    <select name="sort" class="px-2.5 py-1 text-xs border border-slate-300 rounded-lg bg-white">
                        <option value="created_at" {{ request('sort', 'created_at') === 'created_at' ? 'selected' : '' }}>Tanggal Dibuat</option>
                        <option value="expected_arrival_at" {{ request('sort') === 'expected_arrival_at' ? 'selected' : '' }}>Target ETA</option>
                        <option value="shipment_number" {{ request('sort') === 'shipment_number' ? 'selected' : '' }}>Nomor Resi</option>
                        <option value="shipping_cost" {{ request('sort') === 'shipping_cost' ? 'selected' : '' }}>Biaya Kirim</option>
                    </select>
                    <select name="direction" class="px-2.5 py-1 text-xs border border-slate-300 rounded-lg bg-white">
                        <option value="desc" {{ request('direction', 'desc') === 'desc' ? 'selected' : '' }}>Terbaru &darr;</option>
                        <option value="asc" {{ request('direction') === 'asc' ? 'selected' : '' }}>Terlama &uarr;</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.shipments.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                        Reset Filter
                    </a>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Shipments Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200 bg-slate-50/60 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-800">
                Ditemukan <span class="text-indigo-600 font-black">{{ $shipments->total() }}</span> Resi Terdaftar
            </h3>
            <span class="text-xs text-slate-500 font-medium">Halaman {{ $shipments->currentPage() }} dari {{ $shipments->lastPage() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider border-b border-slate-200 font-bold">
                        <th class="px-5 py-3.5">No. Resi & Kargo</th>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-5 py-3.5">Carrier</th>
                        <th class="px-5 py-3.5">Rute (Asal &rarr; Tujuan)</th>
                        <th class="px-5 py-3.5">ETA Target</th>
                        <th class="px-5 py-3.5">Status & Kondisi</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($shipments as $shipment)
                        @php
                            $isDelayed = $shipment->status !== 'DELIVERED' && $shipment->expected_arrival_at && $shipment->expected_arrival_at->isPast();
                            $hasOpenExceptions = $shipment->exceptions->whereIn('status', ['OPEN', 'INVESTIGATING'])->count() > 0;
                            $statusBadge = match($shipment->status) {
                                'DELIVERED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'IN_TRANSIT' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                'EXCEPTION' => 'bg-rose-100 text-rose-800 border-rose-200 animate-pulse',
                                'BOOKED' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'PICKED_UP' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Resi -->
                            <td class="px-5 py-3.5">
                                <a href="{{ route('admin.shipments.show', $shipment) }}" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                                    {{ $shipment->shipment_number }}
                                </a>
                                <p class="text-[11px] text-slate-400 mt-0.5 capitalize">{{ $shipment->cargo_type }} &bull; {{ number_format($shipment->weight_kg, 0) }} kg</p>
                            </td>

                            <!-- Pelanggan -->
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-slate-800 block">{{ $shipment->customer->name ?? '-' }}</span>
                                <span class="text-[11px] text-slate-400">{{ $shipment->customer->email ?? '' }}</span>
                            </td>

                            <!-- Carrier -->
                            <td class="px-5 py-3.5">
                                <span class="font-semibold text-slate-800 block">{{ $shipment->carrier->name ?? '-' }}</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $shipment->carrier->code ?? '' }}</span>
                            </td>

                            <!-- Rute -->
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-slate-800 flex items-center gap-1.5">
                                    <span class="text-indigo-600 font-bold">{{ $shipment->origin->code ?? 'N/A' }}</span>
                                    <span class="text-slate-400">&rarr;</span>
                                    <span class="text-emerald-600 font-bold">{{ $shipment->destination->code ?? 'N/A' }}</span>
                                </div>
                                <span class="text-[10px] text-slate-400">{{ $shipment->origin->city ?? '' }} to {{ $shipment->destination->city ?? '' }}</span>
                            </td>

                            <!-- ETA Target -->
                            <td class="px-5 py-3.5">
                                @if($shipment->expected_arrival_at)
                                    <span class="font-semibold text-slate-800 block">{{ $shipment->expected_arrival_at->format('d M Y, H:i') }}</span>
                                    @if($isDelayed)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200 mt-0.5">
                                            ⚠️ Terlambat
                                        </span>
                                    @else
                                        <span class="text-[10px] text-emerald-600 font-semibold">Tepat Waktu</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Belum ditentukan</span>
                                @endif
                            </td>

                            <!-- Status & Kondisi -->
                            <td class="px-5 py-3.5">
                                <div class="flex flex-col items-start gap-1">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase {{ $statusBadge }}">
                                        {{ $shipment->status }}
                                    </span>
                                    @if($hasOpenExceptions)
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-black bg-rose-600 text-white uppercase tracking-wider">
                                            Incident Hold
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.shipments.show', $shipment) }}" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition">
                                        Detail
                                    </a>
                                    <a href="{{ route('admin.shipments.edit', $shipment) }}" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-bold transition">
                                        Edit
                                    </a>
                                    <a href="{{ route('admin.shipments.print', $shipment->id) }}" target="_blank" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition" title="Print Resi">
                                        <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </a>
                                    @if(strtoupper($shipment->status) === 'CREATED')
                                        <form action="{{ route('admin.shipments.destroy', $shipment->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete(this)" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-bold transition">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic">
                                Tidak ada data pengiriman yang cocok dengan filter pencarian.
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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(button) {
            Swal.fire({
                title: 'Hapus Data Resi?',
                text: "Resi pengiriman berstatus CREATED akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        }
    </script>
@endsection
