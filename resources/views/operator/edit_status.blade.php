<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status - CargoTrace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">
    <!-- Navbar (Tema Emerald) -->
    <nav class="bg-emerald-600 text-white p-4 shadow-md flex justify-between items-center">
        <div class="font-bold text-xl tracking-wide">CargoTrace <span class="text-sm bg-emerald-700 px-2 py-1 rounded ml-2">Operator</span></div>
        <a href="{{ route('operator.dashboard') }}" class="text-emerald-100 hover:text-white text-sm font-medium transition">&larr; Kembali ke Dashboard</a>
    </nav>

    <!-- Main Content -->
    <main class="p-8 max-w-2xl mx-auto w-full mt-4">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">Update Status Pengiriman</h1>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <!-- Info Resi -->
            <div class="mb-6 pb-6 border-b border-slate-100">
                <p class="text-sm text-slate-500 mb-1">Nomor Resi</p>
                <h2 class="text-xl font-bold text-emerald-700">{{ $shipment->shipment_number }}</h2>
                <div class="flex gap-4 mt-3 text-sm text-slate-600">
                    <p><b>Asal:</b> {{ $shipment->origin->name ?? '-' }}</p>
                    <p><b>Tujuan:</b> {{ $shipment->destination->name ?? '-' }}</p>
                </div>
            </div>

            <!-- Form Update -->
            <form action="{{ route('operator.shipments.update', $shipment->shipment_number) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Pilih Status (Enum yang diizinkan) -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Pembaruan Status</label>
                    <select name="status" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500" required>
                        <option value="CREATED" {{ $shipment->status == 'CREATED' ? 'selected' : '' }}>CREATED - Manifes Dibuat</option>
                        <option value="IN_TRANSIT" {{ $shipment->status == 'IN_TRANSIT' ? 'selected' : '' }}>IN_TRANSIT - Dalam Perjalanan</option>
                        <option value="ARRIVED" {{ $shipment->status == 'ARRIVED' ? 'selected' : '' }}>ARRIVED - Tiba di Gudang Tujuan</option>
                        <option value="DELIVERED" {{ $shipment->status == 'DELIVERED' ? 'selected' : '' }}>DELIVERED - Berhasil Dikirim ke Pelanggan</option>
                        <option value="DELAYED" {{ $shipment->status == 'DELAYED' ? 'selected' : '' }}>DELAYED - Mengalami Keterlambatan</option>
                    </select>
                </div>

                <!-- Tambah Catatan (Exception) -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Catatan Lapangan (Opsional)</label>
                    <textarea name="exception_note" rows="3" placeholder="Contoh: Terjadi cuaca buruk, pengiriman tertunda 2 jam..." class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"></textarea>
                    <p class="text-xs text-slate-400 mt-1">Isi jika terjadi kendala, kerusakan, atau catatan penting (exception).</p>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex justify-end gap-3">
                    <a href="{{ route('operator.dashboard') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold transition">Batal</a>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold transition shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>