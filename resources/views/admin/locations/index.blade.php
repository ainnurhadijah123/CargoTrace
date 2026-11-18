@extends('layouts.admin')

@section('title', 'Kelola Lokasi - Admin')
@section('header_title', 'Daftar Lokasi & Gudang')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">Semua Lokasi / Gudang</h3>
            
            <a href="{{ route('admin.locations.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Lokasi
            </a>
        </div>

        <!-- Pesan Sukses -->
        @if (session('status'))
            <div class="p-4 m-6 mb-0 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif

        <!-- Pesan Error / Peringatan (Misal: Lokasi masih terikat pengiriman) -->
        @if (session('error'))
            <div class="p-4 m-6 mb-0 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-x-auto p-6">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 font-semibold">Kode</th>
                        <th class="px-6 py-4 font-semibold">Nama Lokasi</th>
                        <th class="px-6 py-4 font-semibold">Kota</th>
                        <th class="px-6 py-4 font-semibold">Koordinat (Lat, Lng)</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700 text-sm">
                    @forelse ($locations as $location)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4 font-bold text-indigo-600">{{ $location->code }}</td>
                            <td class="px-6 py-4 font-medium text-slate-800">{{ $location->name }}</td>
                            <td class="px-6 py-4">{{ $location->city }}</td>
                            <td class="px-6 py-4 text-xs font-mono text-slate-500">
                                {{ $location->latitude ?? '-' }}, {{ $location->longitude ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.locations.edit', $location->id) }}" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-md text-xs font-semibold transition">Edit</a>
                                    
                                    <form action="{{ route('admin.locations.destroy', $location->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDelete(this)" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-md text-xs font-semibold transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">Belum ada data lokasi tersimpan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($locations, 'hasPages') && $locations->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $locations->links() }}
            </div>
        @endif
    </div>

    <!-- Script SweetAlert2 untuk konfirmasi hapus -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(button) {
            Swal.fire({
                title: 'Hapus Lokasi?',
                text: "Data lokasi ini akan dihapus dari sistem.",
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