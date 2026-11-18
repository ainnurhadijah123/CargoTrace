@extends('layouts.admin')

@section('title', 'Kelola Pengguna - Admin')
@section('header_title', 'Daftar Pengguna Sistem')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">Semua Pengguna</h3>
            
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Pengguna
            </a>
        </div>

        @if (session('success'))
            <div class="p-4 m-6 mb-0 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 m-6 mb-0 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 font-semibold">Nama Pengguna</th>
                        <th class="px-6 py-4 font-semibold">Email</th>
                        <th class="px-6 py-4 font-semibold">Peran (Role)</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700 text-sm">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4 font-bold text-slate-800">{{ $user->name }}</td>
                            <td class="px-6 py-4">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                @php
                                    // Pewarnaan badge berdasarkan role
                                    $roleColor = match(is_object($user->role) ? $user->role->value : $user->role) {
                                        'ADMIN' => 'bg-rose-100 text-rose-800',
                                        'OPERATOR' => 'bg-blue-100 text-blue-800',
                                        'CUSTOMER' => 'bg-emerald-100 text-emerald-800',
                                        default => 'bg-slate-100 text-slate-800'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $roleColor }}">
                                    {{ is_object($user->role) ? $user->role->value : $user->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-md text-xs font-semibold transition">Edit</a>
                                    
                                    @if(auth()->id() !== $user->id)
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete(this)" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-md text-xs font-semibold transition">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">Belum ada data pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Script SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(button) {
            Swal.fire({
                title: 'Hapus Pengguna?',
                text: "Akun pengguna ini akan dihapus secara permanen dari sistem.",
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