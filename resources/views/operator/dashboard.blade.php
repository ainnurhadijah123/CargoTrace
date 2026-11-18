<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Operator - CargoTrace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">
    <!-- Navbar (Tema Teal) -->
    <nav class="bg-teal-700 text-white p-4 shadow-md flex justify-between items-center">
        <div class="font-bold text-xl tracking-wide">CargoTrace Operator</div>
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
    <main class="flex-grow p-8 max-w-6xl mx-auto w-full mt-4">
        <div class="flex justify-between items-end mb-6">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Panel Logistik (Manifes)</h1>
                <p class="text-slate-500 mt-1">Pantau rute dan perbarui status kargo secara real-time.</p>
            </div>
        </div>

        <!-- Notifikasi Sukses -->
        @if (session('status'))
            <div class="mb-6 p-4 bg-teal-50 border border-teal-200 text-teal-700 rounded-lg text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif

        <!-- Tabel Daftar Pengiriman -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto p-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                            <th class="px-6 py-4 font-semibold">No. Resi</th>
                            <th class="px-6 py-4 font-semibold">Pelanggan</th>
                            <th class="px-6 py-4 font-semibold">Rute (Asal &rarr; Tujuan)</th>
                            <th class="px-6 py-4 font-semibold">Status Saat Ini</th>
                            <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700 text-sm">
                        @forelse ($shipments as $shipment)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-6 py-4 font-bold text-teal-700">{{ $shipment->shipment_number }}</td>
                                <td class="px-6 py-4 font-medium">{{ $shipment->customer->name ?? 'TBA' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="truncate max-w-[100px]">{{ $shipment->origin->code ?? '-' }}</span>
                                        <span class="text-slate-400">&rarr;</span>
                                        <span class="truncate max-w-[100px]">{{ $shipment->destination->code ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-slate-100 border border-slate-200 text-slate-700 rounded text-xs font-bold uppercase">
                                        {{ $shipment->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Tombol Detail -->
                                        <a href="{{ route('operator.shipments.show', $shipment->shipment_number) }}" class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-200">
                                            Lihat Detail
                                        </a>
                                        <!-- Tombol Update Status -->
                                        <a href="{{ route('operator.shipments.edit', $shipment->shipment_number) }}" class="px-3 py-1.5 bg-teal-50 hover:bg-teal-100 text-teal-700 rounded-lg text-xs font-semibold transition border border-teal-200">
                                            Update Status
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">Belum ada data resi pengiriman di dalam sistem.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 p-4 text-center text-sm text-slate-500">
        &copy; {{ date('Y') }} CargoTrace. All rights reserved.
    </footer>
</body>
</html>