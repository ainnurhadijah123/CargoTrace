@extends('layouts.admin')

@section('title', 'Kelola Carrier - Admin')
@section('header_title', 'Kelola Armada / Maskapai')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">Daftar Armada (Carrier)</h3>
            <!-- PERUBAHAN: admin.carriers.create -->
            <a href="{{ route('admin.carriers.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">
                + Tambah Armada
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 font-semibold">Kode</th>
                        <th class="px-6 py-4 font-semibold">Nama Armada</th>
                        <th class="px-6 py-4 font-semibold">Kontak</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700 text-sm">
                    @forelse($carriers as $carrier)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4 font-bold text-blue-600">{{ $carrier->code }}</td>
                            <td class="px-6 py-4 font-medium text-slate-800">{{ $carrier->name }}</td>
                            <td class="px-6 py-4">{{ $carrier->contact_info ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- PERUBAHAN: admin.carriers.edit -->
                                    <a href="{{ route('admin.carriers.edit', $carrier->id) }}" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-md text-xs font-semibold transition">Edit</a>
                                    
                                    <!-- PERUBAHAN: admin.carriers.destroy -->
                                    <form action="{{ route('admin.carriers.destroy', $carrier->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <!-- Hapus onsubmit di form, lalu ubah type tombol jadi button dan tambahkan onclick -->
                                        <button type="button" onclick="confirmDelete(this)" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-md text-xs font-semibold transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">Belum ada data carrier.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(button) {
            Swal.fire({
                title: 'Hapus Armada?',
                text: "Data armada ini akan dihapus dari sistem dan tidak dapat dikembalikan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48', // Warna rose-600 Tailwind
                cancelButtonColor: '#94a3b8',  // Warna slate-400 Tailwind
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika user klik "Ya, Hapus!", cari form terdekat lalu submit
                    button.closest('form').submit();
                }
            })
        }
    </script>
@endsection