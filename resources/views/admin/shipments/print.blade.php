<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Resi - {{ $shipment->shipment_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* CSS khusus saat dicetak ke kertas */
        @media print {
            @page { margin: 1cm; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100 text-black">

    <!-- Tombol Print Manual (Akan hilang saat kertas dicetak) -->
    <div class="no-print p-4 bg-slate-800 text-center text-white">
        <p class="mb-3 text-sm">Pastikan pengaturan printer (ukuran kertas & margin) sudah sesuai.</p>
        <button onclick="window.print()" class="bg-indigo-500 hover:bg-indigo-600 px-6 py-2 rounded-lg font-bold">Cetak Sekarang</button>
        <a href="{{ route('admin.shipments.index') }}" class="ml-4 text-slate-300 hover:text-white underline text-sm">Kembali</a>
    </div>

    <!-- Area Kertas Resi (A4/Letter) -->
    <div class="bg-white max-w-3xl mx-auto my-8 p-10 shadow-lg border border-gray-200">
        <!-- Header Perusahaan -->
        <div class="flex justify-between items-start border-b-2 border-slate-800 pb-6 mb-6">
            <div>
                <h1 class="text-3xl font-extrabold tracking-widest uppercase">CargoTrace</h1>
                <p class="text-sm text-gray-500 mt-1">Sistem Pelacakan Kargo Digital</p>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold uppercase tracking-widest">Resi Pengiriman</h2>
                <p class="text-sm text-gray-600 font-mono mt-1">{{ $shipment->shipment_number }}</p>
                <!-- Barcode Palsu (Hanya untuk estetika UI) -->
                <div class="mt-2 text-[40px] font-mono tracking-tighter leading-none">*{{ $shipment->shipment_number }}*</div>
            </div>
        </div>

        <!-- Info Asal & Tujuan (SUDAH DIRAPIKAN) -->
        <div class="grid grid-cols-2 gap-8 mb-8 border-b border-gray-200 pb-8">
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pengirim (Asal)</h3>
                <p class="font-bold text-lg uppercase">{{ $shipment->origin->code ?? '-' }} - {{ $shipment->origin->name ?? '-' }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ $shipment->origin->address ?? 'Alamat Asal' }}</p>
            </div>
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Penerima (Tujuan)</h3>
                <p class="font-bold text-lg uppercase">{{ $shipment->customer->name ?? 'TBA' }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ $shipment->destination->code ?? '-' }} - {{ $shipment->destination->name ?? '-' }}</p>
                <p class="text-sm text-gray-600">{{ $shipment->destination->address ?? 'Alamat Tujuan' }}</p>
            </div>
        </div>

        <!-- Rincian Kargo (DENGAN BIAYA PENGIRIMAN) -->
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Rincian Paket & Biaya</h3>
        <table class="w-full text-left mb-8 border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 p-3 text-sm font-semibold">Tipe Kargo</th>
                    <th class="border border-gray-300 p-3 text-sm font-semibold text-center">Berat (Kg)</th>
                    <th class="border border-gray-300 p-3 text-sm font-semibold">Armada (Carrier)</th>
                    <th class="border border-gray-300 p-3 text-sm font-semibold text-right">Biaya Pengiriman</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-gray-300 p-3 text-sm capitalize">{{ $shipment->cargo_type }}</td>
                    <td class="border border-gray-300 p-3 text-sm text-center">{{ $shipment->weight_kg }}</td>
                    <td class="border border-gray-300 p-3 text-sm">{{ $shipment->carrier->name ?? '-' }}</td>
                    <td class="border border-gray-300 p-3 text-sm text-right font-mono">
                        <!-- Jika IDR tampilkan Rp dengan format titik, jika USD tampilkan $ dengan 2 desimal -->
                        @if(($shipment->currency ?? 'IDR') == 'USD')
                            $ {{ number_format($shipment->shipping_cost ?? 0, 2, '.', ',') }}
                        @else
                            Rp {{ number_format($shipment->shipping_cost ?? 0, 0, ',', '.') }}
                        @endif
                    </td>
                </tr>
                <!-- Baris Total -->
                <tr class="bg-gray-50">
                    <td colspan="3" class="border border-gray-300 p-3 text-sm font-bold text-right">TOTAL PEMBAYARAN</td>
                    <td class="border border-gray-300 p-3 text-base font-bold text-right">
                       <!-- Jika IDR tampilkan Rp dengan format titik, jika USD tampilkan $ dengan 2 desimal -->
                        @if(($shipment->currency ?? 'IDR') == 'USD')
                            $ {{ number_format($shipment->shipping_cost ?? 0, 2, '.', ',') }}
                        @else
                            Rp {{ number_format($shipment->shipping_cost ?? 0, 0, ',', '.') }}
                        @endif
                </tr>
            </tbody>
        </table>

        <!-- Footer / Tanda Tangan -->
        <div class="flex justify-between items-end mt-12 pt-8 text-sm">
            <div>
                <p class="text-gray-500 mb-10">Tanda Tangan Pengirim</p>
                <p class="font-bold border-t border-gray-400 pt-1 w-48 text-center">( ..................................... )</p>
            </div>
            <div>
                <p class="text-gray-500 mb-10">Petugas / Operator</p>
                <p class="font-bold border-t border-gray-400 pt-1 w-48 text-center">{{ auth()->user()->name }}</p>
            </div>
        </div>
        
        <p class="text-center text-xs text-gray-400 mt-12 border-t border-gray-200 pt-4">
            Dicetak pada: {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}
        </p>
    </div>

    <!-- Script otomatis buka dialog print saat halaman dimuat -->
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>