@extends('layouts.admin')

@section('title', 'Daftar Pengiriman - Admin')
@section('header_title', 'Daftar Pengiriman Kargo')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">Semua Data Resi</h3>
            
            <!-- PERUBAHAN: admin.shipments.create -->
            <a href="{{ route('admin.shipments.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Buat Resi Baru
            </a>
        </div>

        @if (session('status'))
            <div class="p-4 m-6 mb-0 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 font-semibold">No. Resi</th>
                        <th class="px-6 py-4 font-semibold">Pelanggan</th>
                        <th class="px-6 py-4 font-semibold">Maskapai</th>
                        <th class="px-6 py-4 font-semibold">Asal &rarr; Tujuan</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700 text-sm">
                    @forelse ($shipments as $shipment)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4 font-bold text-indigo-600">{{ $shipment->shipment_number }}</td>
                            <td class="px-6 py-4 font-medium text-slate-800">{{ $shipment->customer->name ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $shipment->carrier->name ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $shipment->origin->name ?? '-' }} &rarr; {{ $shipment->destination->name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                    {{ is_object($shipment->status) ? $shipment->status->value : $shipment->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- PERUBAHAN: admin.shipments.show dan edit -->
                                    <a href="{{ route('admin.shipments.show', $shipment) }}" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-md text-xs font-semibold transition">Detail</a>
                                    <a href="{{ route('admin.shipments.edit', $shipment) }}" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-md text-xs font-semibold transition">Edit</a>
                                    <!-- PERUBAHAN: Menambahkan Form Hapus dengan SweetAlert -->
                                    <form action="{{ route('admin.shipments.destroy', $shipment->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDelete(this)" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-md text-xs font-semibold transition">Hapus</button>
                                    </form>
                                    <a href="{{ route('admin.shipments.print', $shipment->id) }}" target="_blank" class="px-3 py-1 bg-indigo-100 text-indigo-700 hover:bg-indigo-200 rounded text-xs font-bold mr-1">
                                        Cetak
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">Belum ada data pengiriman kargo.</td>
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

    <!-- Script SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(button) {
            Swal.fire({
                title: 'Hapus Data Resi?',
                text: "Resi pengiriman ini akan dihapus permanen. Aksi ini hanya bisa dilakukan jika status masih 'Created'.",
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
            })
        }
    </script>
@endsection