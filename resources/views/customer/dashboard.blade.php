<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Customer - CargoTrace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">
    <!-- Navbar -->
    <nav class="bg-indigo-600 text-white p-4 shadow-md flex justify-between items-center">
        <div class="font-bold text-xl tracking-wide">CargoTrace</div>
        <div class="flex items-center gap-4">
            <span class="font-medium">Halo, {{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST" class="inline m-0">
                @csrf
                <button type="submit" class="bg-rose-500 hover:bg-rose-600 px-4 py-2 rounded-lg text-sm font-semibold transition shadow-sm">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow p-8 max-w-4xl mx-auto w-full mt-4">
        <h1 class="text-3xl font-bold mb-6 text-slate-800">Lacak Kiriman Anda</h1>
        
        <!-- Form Pencarian Resi -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6">
            <form action="#" method="GET" class="flex gap-4">
                <input type="text" name="resi" placeholder="Masukkan nomor resi (cth: CT-20261112-DIXV0D)..." class="flex-grow border border-slate-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2.5 rounded-lg font-semibold transition shadow-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Lacak
                </button>
            </form>
        </div>

        <!-- Daftar Kiriman Customer -->
        <h2 class="text-xl font-bold text-slate-800 mb-4 mt-8">Riwayat Pengiriman Anda</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse ($shipments as $shipment)
                <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-1 rounded border border-indigo-100">{{ $shipment->shipment_number }}</span>
                            <h3 class="font-semibold text-slate-800 mt-2 capitalize">{{ $shipment->cargo_type }} - {{ $shipment->weight_kg }} Kg</h3>
                        </div>
                        <span class="px-2 py-1 rounded text-xs font-bold bg-slate-100 border border-slate-200">{{ $shipment->status }}</span>
                    </div>
                    
                    <div class="text-sm text-slate-600 mb-4 space-y-1">
                        <p><span class="font-medium text-slate-500">Asal:</span> {{ $shipment->origin->name ?? 'TBA' }}</p>
                        <p><span class="font-medium text-slate-500">Tujuan:</span> {{ $shipment->destination->name ?? 'TBA' }}</p>
                        <p><span class="font-medium text-slate-500">ETA:</span> {{ \Carbon\Carbon::parse($shipment->expected_arrival_at)->format('d M Y, H:i') }}</p>
                    </div>

                    <a href="{{ route('customer.shipments.show', $shipment->shipment_number) }}" class="block text-center w-full bg-slate-50 hover:bg-slate-100 text-indigo-600 font-semibold py-2 rounded-lg border border-slate-200 transition text-sm">
                        Lihat Detail & Lacak
                    </a>
                </div>
            @empty
                <div class="col-span-full text-center py-8 text-slate-500 bg-white rounded-xl border border-slate-200 border-dashed">
                    Belum ada riwayat pengiriman untuk akun Anda.
                </div>
            @endforelse
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 p-4 text-center text-sm text-slate-500">
        &copy; {{ date('Y') }} CargoTrace. All rights reserved.
    </footer>
</body>
</html>