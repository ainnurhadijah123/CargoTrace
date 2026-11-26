<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan - GoTrace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">
    <!-- Navbar -->
    <nav class="bg-indigo-600 text-white p-4 shadow-md flex justify-between items-center sticky top-0 z-30">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.dashboard') }}" class="font-black text-xl tracking-wide flex items-center gap-1.5">
                GoTrace<span class="text-indigo-300">.</span>
            </a>
            <span class="text-xs font-bold bg-indigo-700 text-indigo-100 px-2.5 py-1 rounded-lg uppercase tracking-wider">Customer Portal</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-xs font-medium text-indigo-100 hidden sm:inline">Halo, {{ auth()->user()->name }}</span>
            <a href="{{ route('track.index') }}" target="_blank" class="text-xs font-semibold text-indigo-100 hover:text-white underline">
                Lacak Resi Publik &nearr;
            </a>
            <form action="{{ route('logout') }}" method="POST" class="inline m-0">
                @csrf
                <button type="submit" class="bg-rose-500 hover:bg-rose-600 px-3.5 py-1.5 rounded-lg text-xs font-bold transition shadow-sm">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow p-6 md:p-8 max-w-6xl mx-auto w-full">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight">Status & Visibilitas Kiriman Anda</h1>
                <p class="text-xs md:text-sm text-slate-500 mt-1">Pantau perjalanan kargo internasional dan domestik Anda secara transparan.</p>
            </div>
            @if(isset($unreadCount) && $unreadCount > 0)
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200 self-start sm:self-auto">
                    🔔 {{ $unreadCount }} Pemberitahuan Baru
                </span>
            @endif
        </div>

        <!-- Alert Session -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Customer Summary Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3.5">
                <div class="p-3 bg-indigo-50 text-indigo-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <div>
                    <span class="text-[10px] md:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Kiriman</span>
                    <span class="text-xl md:text-2xl font-black text-slate-800">{{ $totalMyShipments ?? 0 }}</span>
                </div>
            </div>

            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3.5">
                <div class="p-3 bg-teal-50 text-teal-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <span class="text-[10px] md:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Dalam Transit</span>
                    <span class="text-xl md:text-2xl font-black text-teal-700">{{ $myInTransit ?? 0 }}</span>
                </div>
            </div>

            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3.5">
                <div class="p-3 bg-emerald-50 text-emerald-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <span class="text-[10px] md:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Terkirim</span>
                    <span class="text-xl md:text-2xl font-black text-emerald-700">{{ $myDelivered ?? 0 }}</span>
                </div>
            </div>

            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3.5">
                <div class="p-3 bg-amber-50 text-amber-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <span class="text-[10px] md:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Perlu Perhatian</span>
                    <span class="text-xl md:text-2xl font-black text-amber-700">{{ $myDelayedOrException ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- In-App Notifications Section (Slide 10 Requirement) -->
        @if(isset($notifications) && $notifications->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-6">
                <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🔔</span>
                        <h3 class="text-sm font-bold text-slate-800">Pemberitahuan & Notifikasi Terbaru</h3>
                    </div>
                    <form action="{{ route('notifications.markAllRead') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">
                            Tandai Semua Dibaca
                        </button>
                    </form>
                </div>
                <div class="space-y-2.5">
                    @foreach($notifications as $notif)
                        @php
                            $badgeColor = match($notif->type) {
                                'DANGER' => 'bg-rose-100 text-rose-800 border-rose-200',
                                'SUCCESS' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                default => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                            };
                        @endphp
                        <div class="p-3 rounded-xl border {{ $notif->isRead() ? 'bg-slate-50 border-slate-200 text-slate-500' : 'bg-white border-indigo-200 shadow-sm' }} flex justify-between items-start gap-3 text-xs">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase {{ $badgeColor }}">{{ $notif->type }}</span>
                                    <h4 class="font-bold text-slate-800">{{ $notif->title }}</h4>
                                    <span class="text-[10px] text-slate-400">&bull; {{ $notif->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-slate-600 text-[11px]">{{ $notif->message }}</p>
                            </div>
                            @if(!$notif->isRead())
                                <form action="{{ route('notifications.read', $notif) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-[10px] font-bold text-slate-400 hover:text-indigo-600 bg-slate-100 hover:bg-indigo-50 px-2 py-1 rounded transition">
                                        Dibaca &check;
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Quick Resi Search & Filter -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <form action="{{ route('customer.dashboard') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-grow">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan nomor resi (cth: CT-20261118-SEAEUR) atau nama kargo..." 
                        class="w-full text-xs pl-9 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <select name="status" class="text-xs px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                    <option value="">Semua Status</option>
                    @foreach(['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'EXCEPTION'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                    Cari Kiriman
                </button>
            </form>
        </div>

        <!-- Daftar Kartu Pengiriman Customer -->
        <h2 class="text-lg font-bold text-slate-800 mb-4">Daftar Kiriman Terdaftar</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @forelse ($shipments as $shipment)
                @php
                    $isDelayed = $shipment->status !== 'DELIVERED' && $shipment->expected_arrival_at && $shipment->expected_arrival_at->isPast();
                    $hasOpenException = $shipment->exceptions->whereIn('status', ['OPEN', 'INVESTIGATING'])->first();
                    $statusBadge = match($shipment->status) {
                        'DELIVERED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'IN_TRANSIT' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                        'EXCEPTION' => 'bg-rose-100 text-rose-800 border-rose-200 animate-pulse',
                        'BOOKED' => 'bg-blue-100 text-blue-800 border-blue-200',
                        'PICKED_UP' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                    };
                @endphp
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <!-- Header Card -->
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="text-xs font-extrabold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                                    {{ $shipment->shipment_number }}
                                </span>
                                <h3 class="font-bold text-slate-800 text-sm mt-2 capitalize">{{ $shipment->cargo_type }}</h3>
                                <p class="text-[11px] text-slate-400">{{ number_format($shipment->weight_kg, 1) }} kg &bull; Carrier: {{ $shipment->carrier->name ?? '-' }}</p>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black border uppercase {{ $statusBadge }}">
                                {{ $shipment->status }}
                            </span>
                        </div>

                        <!-- Route & Schedule -->
                        <div class="text-xs text-slate-600 my-4 space-y-1.5 p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <div class="flex justify-between items-center font-semibold">
                                <span class="text-teal-700 font-bold">📍 {{ $shipment->origin->name ?? 'TBA' }} ({{ $shipment->origin->city ?? '' }})</span>
                                <span class="text-slate-400">&rarr;</span>
                                <span class="text-indigo-700 font-bold">🏁 {{ $shipment->destination->name ?? 'TBA' }} ({{ $shipment->destination->city ?? '' }})</span>
                            </div>
                            <div class="flex justify-between items-center text-[11px] pt-1.5 border-t border-slate-200/60">
                                <span class="text-slate-500">Target ETA:</span>
                                <span class="font-bold text-slate-700">
                                    {{ $shipment->expected_arrival_at ? \Carbon\Carbon::parse($shipment->expected_arrival_at)->format('d M Y, H:i') : 'Pending' }}
                                </span>
                            </div>
                        </div>

                        <!-- Exception Alert if any -->
                        @if($hasOpenException)
                            <div class="mb-4 p-2.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs">
                                <div class="flex items-center gap-1.5 font-bold mb-0.5">
                                    <span class="w-2 h-2 rounded-full bg-rose-600 animate-ping"></span>
                                    <span>Pemberitahuan Kendala: {{ str_replace('_', ' ', $hasOpenException->type) }}</span>
                                </div>
                                <p class="text-[11px] text-rose-700 line-clamp-1">{{ $hasOpenException->description }}</p>
                            </div>
                        @elseif($isDelayed)
                            <div class="mb-4 p-2 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs font-semibold flex items-center gap-1.5">
                                <span>⚠️ Pengiriman terindikasi mengalami keterlambatan jadwal.</span>
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('customer.shipments.show', $shipment->shipment_number) }}" class="block text-center w-full bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white font-bold py-2.5 rounded-xl transition text-xs border border-indigo-200">
                        Lihat Peta Rute & Timeline Lengkap &rarr;
                    </a>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-slate-400 bg-white rounded-2xl border border-slate-200 border-dashed">
                    Tidak ada riwayat pengiriman kargo yang sesuai untuk akun Anda.
                </div>
            @endforelse
        </div>

        @if($shipments->hasPages())
            <div class="mt-6">
                {{ $shipments->links() }}
            </div>
        @endif
    </main>
    
    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 p-4 text-center text-xs text-slate-500 mt-8">
        &copy; {{ date('Y') }} GoTrace &bull; Global Shipment Visibility & Tracking Platform
    </footer>
</body>
</html>
