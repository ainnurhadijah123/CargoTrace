@extends('layouts.admin')

@section('title', 'Audit Log - Admin')
@section('header_title', 'Riwayat Aktivitas Sistem (Audit Log)')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-800">Log Aktivitas Pengguna</h3>
        </div>

        <div class="overflow-x-auto p-6">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 font-semibold">Waktu</th>
                        <th class="px-6 py-4 font-semibold">Pengguna</th>
                        <th class="px-6 py-4 font-semibold">Aksi</th>
                        <th class="px-6 py-4 font-semibold">Modul</th>
                        <th class="px-6 py-4 font-semibold">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700 text-sm">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4 text-xs text-slate-500">{{ $log->created_at->format('d M Y, H:i:s') }}</td>
                            <td class="px-6 py-4 font-medium text-slate-800">{{ $log->user->name ?? 'Sistem / Tamu' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-slate-100 text-slate-800 rounded text-xs font-bold">{{ $log->action }}</span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-indigo-600">{{ $log->model }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">Belum ada riwayat aktivitas tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection