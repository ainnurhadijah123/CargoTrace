<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status Resi {{ $shipment->shipment_number }} - Operator GoTrace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen selection:bg-teal-700 selection:text-white">

    <!-- Navbar Operator (Modern Teal Theme) -->
    <nav class="bg-teal-700 text-white p-4 shadow-md flex justify-between items-center sticky top-0 z-30">
        <div class="flex items-center gap-3">
            <a href="{{ route('operator.dashboard') }}" class="font-black text-xl tracking-wide flex items-center gap-2">
                GoTrace<span class="text-teal-300">.</span>
            </a>
            <span class="text-xs font-bold bg-teal-800 text-teal-200 px-2.5 py-1 rounded-lg uppercase tracking-wider font-mono">Logistics Operator</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-xs font-semibold text-teal-100 hidden sm:inline">Petugas: {{ auth()->user()->name }}</span>
            <a href="{{ route('operator.shipments.show', $shipment->shipment_number) }}" class="text-xs font-bold text-teal-100 hover:text-white transition flex items-center gap-1">
                &larr; Detail Resi
            </a>
            <form action="{{ route('logout') }}" method="POST" class="inline m-0">
                @csrf
                <button type="submit" class="bg-teal-800 hover:bg-rose-600 px-3.5 py-1.5 rounded-lg text-xs font-bold transition shadow-sm">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="flex-grow p-4 sm:p-6 md:p-8 max-w-4xl mx-auto w-full">
        
        <!-- Breadcrumb & Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-2">
                <a href="{{ route('operator.dashboard') }}" class="hover:text-teal-700 transition">Dashboard Operasional</a>
                <span>/</span>
                <a href="{{ route('operator.shipments.show', $shipment->shipment_number) }}" class="hover:text-teal-700 transition font-mono">{{ $shipment->shipment_number }}</a>
                <span>/</span>
                <span class="text-slate-600">Update Status</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">Pembaruan Status Pengiriman</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Rekam checkpoint perjalanan logistik dan transisi status pengiriman secara deterministik.</p>
                </div>
                <a href="{{ route('operator.shipments.show', $shipment->shipment_number) }}" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 self-start sm:self-auto">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Detail</span>
                </a>
            </div>
        </div>

        <!-- Alert Notifications -->
        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold rounded-2xl flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold rounded-2xl flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Card Rincian Kargo Terkini -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-7 mb-6">
            <div class="flex flex-col sm:flex-row justify-between sm:items-start gap-4 pb-6 border-b border-slate-100">
                <div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-[11px] font-mono font-bold text-slate-400 uppercase tracking-widest">Nomor Resi</span>
                        @php
                            $statusBadge = match($shipment->status) {
                                'DELIVERED' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                'IN_TRANSIT' => 'bg-teal-100 text-teal-800 border-teal-300',
                                'EXCEPTION' => 'bg-rose-100 text-rose-800 border-rose-300 animate-pulse',
                                'BOOKED' => 'bg-blue-100 text-blue-800 border-blue-300',
                                'PICKED_UP' => 'bg-cyan-100 text-cyan-800 border-cyan-300',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-black border uppercase {{ $statusBadge }}">
                            Status Saat Ini: {{ $shipment->status }}
                        </span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black font-mono text-slate-900 tracking-tight">{{ $shipment->shipment_number }}</h2>
                    
                    <!-- Atas Nama Pelanggan (Customer Name Prominent) -->
                    <div class="mt-3 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-teal-50 border border-teal-200/90 text-xs font-bold text-teal-900">
                        <svg class="w-4 h-4 text-teal-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Atas Nama: <strong class="text-teal-950 font-extrabold">{{ $shipment->customer?->name ?? 'Direct Consignee' }}</strong></span>
                        @if($shipment->customer?->email)
                            <span class="text-teal-600 font-normal">({{ $shipment->customer->email }})</span>
                        @endif
                    </div>
                </div>

                <div class="text-left sm:text-right space-y-1">
                    <span class="text-[11px] font-mono font-bold text-slate-400 uppercase tracking-widest block">Carrier Partner</span>
                    <span class="text-sm font-bold text-slate-800 block">{{ $shipment->carrier?->name ?? 'Global Partner' }}</span>
                    <span class="text-xs font-mono text-slate-500 block">Kode: {{ $shipment->carrier?->code ?? '-' }}</span>
                </div>
            </div>

            <!-- Route & Specs Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-5 text-xs">
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block mb-1">Pelabuhan Asal</span>
                    <p class="font-bold text-slate-800 text-sm">{{ $shipment->origin?->name ?? '-' }}</p>
                    <p class="text-slate-500 mt-0.5">{{ $shipment->origin?->city }}, {{ $shipment->origin?->country }}</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block mb-1">Pelabuhan Tujuan</span>
                    <p class="font-bold text-slate-800 text-sm">{{ $shipment->destination?->name ?? '-' }}</p>
                    <p class="text-slate-500 mt-0.5">{{ $shipment->destination?->city }}, {{ $shipment->destination?->country }}</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block mb-1">Spesifikasi Kargo</span>
                    <p class="font-bold text-slate-800 text-sm">{{ $shipment->cargo_type ?? 'General Cargo' }}</p>
                    <p class="text-slate-500 mt-0.5 font-mono">{{ number_format($shipment->weight_kg, 0) }} kg &bull; {{ $shipment->formatted_cost }}</p>
                </div>
            </div>
        </div>

        <!-- Form Update Status & Checkpoint -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-8">
            <div class="flex items-center gap-2.5 mb-6 pb-4 border-b border-slate-100">
                <div class="p-2 rounded-xl bg-teal-50 text-teal-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Form Pembaruan Checkpoint & Status</h3>
                    <p class="text-xs text-slate-500">Pilih status transisi logistik yang valid dan rekam lokasi checkpoint perjalanan.</p>
                </div>
            </div>

            <form action="{{ route('operator.shipments.update', $shipment->shipment_number) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @php
                    // Label ramah pengguna untuk enum status logistik
                    $statusLabels = [
                        'BOOKED' => 'BOOKED — Ruang Muat / Kontainer Dikonfirmasi',
                        'PICKED_UP' => 'PICKED_UP — Kargo Dijemput dari Gudang Asal',
                        'IN_TRANSIT' => 'IN_TRANSIT — Dalam Pelayaran / Perjalanan Interkontinental',
                        'EXCEPTION' => 'EXCEPTION — Terjadi Kendala (Customs Hold / Cuaca / Dokumen)',
                        'DELIVERED' => 'DELIVERED — Kargo Tiba & Diterima Pelanggan (Terminal)',
                        'CANCELLED' => 'CANCELLED — Pengiriman Dibatalkan',
                    ];
                @endphp

                <!-- Pilih Status Lanjutan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 font-mono">
                        Pembaruan Status Lanjutan <span class="text-rose-500">*</span>
                    </label>

                    @if(count($allowedStatuses) > 0)
                        <div class="relative">
                            <select name="status" id="status" required
                                class="w-full bg-[#f8fafc] hover:bg-white focus:bg-white border border-slate-200 focus:border-teal-600 focus:ring-4 focus:ring-teal-600/10 rounded-2xl px-4 py-3 text-slate-800 text-sm font-semibold transition focus:outline-none appearance-none cursor-pointer">
                                <option value="" disabled selected>-- Pilih Status Transisi Berikutnya --</option>
                                @foreach($allowedStatuses as $statusOption)
                                    <option value="{{ $statusOption }}" {{ old('status') === $statusOption ? 'selected' : '' }}>
                                        {{ $statusLabels[$statusOption] ?? $statusOption }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-1.5">Hanya menampilkan opsi transisi yang sah berdasarkan status terkini (<strong class="text-slate-600">{{ $shipment->status }}</strong>).</p>
                    @else
                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Pengiriman ini telah berada pada status terminal (<strong>{{ $shipment->status }}</strong>) dan tidak dapat diubah lagi.</span>
                        </div>
                    @endif
                </div>

                <!-- Lokasi Checkpoint (Hub Pelabuhan) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 font-mono">
                        Lokasi Checkpoint / Hub Pelabuhan (Opsional)
                    </label>
                    <div class="relative">
                        <select name="location_id" id="location_id"
                            class="w-full bg-[#f8fafc] hover:bg-white focus:bg-white border border-slate-200 focus:border-teal-600 focus:ring-4 focus:ring-teal-600/10 rounded-2xl px-4 py-3 text-slate-800 text-sm font-medium transition focus:outline-none appearance-none cursor-pointer">
                            <option value="">-- Pilih Lokasi Checkpoint Saat Ini (Gunakan Titik Saat Ini) --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }}) - [{{ $loc->code }}]
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-1.5">Pilih titik hub pelabuhan atau terminal tempat pemindaian barcode kargo berlangsung.</p>
                </div>

                <!-- Catatan Lapangan / Keterangan Event -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 font-mono">
                        Catatan Lapangan & Log Operasional (Opsional)
                    </label>
                    <textarea name="description" id="description" rows="3" maxlength="500"
                        placeholder="Contoh: Kargo telah selesai melewati pemeriksaan bea cukai dan dimuat ke dalam kapal feeder MV Maersk Singapore..."
                        class="w-full bg-[#f8fafc] hover:bg-white focus:bg-white border border-slate-200 focus:border-teal-600 focus:ring-4 focus:ring-teal-600/10 rounded-2xl px-4 py-3 text-slate-800 text-sm font-medium transition focus:outline-none resize-none">{{ old('description') }}</textarea>
                    <p class="text-xs text-slate-400 mt-1">Catatan ini akan tersimpan permanen pada riwayat pelacakan (audit timeline) dan dapat dilihat oleh pelanggan serta staf logistik.</p>
                </div>

                <!-- Tombol Aksi -->
                <div class="pt-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-center justify-end gap-3">
                    <a href="{{ route('operator.shipments.show', $shipment->shipment_number) }}"
                        class="w-full sm:w-auto px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-2xl text-xs transition duration-200 text-center">
                        Batal
                    </a>
                    @if(count($allowedStatuses) > 0)
                        <button type="submit"
                            class="w-full sm:w-auto px-7 py-3 bg-teal-700 hover:bg-teal-800 active:scale-[0.99] text-white font-bold rounded-2xl text-xs transition duration-200 shadow-md shadow-teal-900/15 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan & Rekam Checkpoint</span>
                        </button>
                    @endif
                </div>
            </form>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-100 py-6 text-center text-xs text-slate-400">
        <p>GoTrace Logistics Visibility Platform &bull; Operator Dispatch Terminal</p>
    </footer>

</body>
</html>
