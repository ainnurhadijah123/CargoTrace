<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - CargoTrace')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden font-sans">

    @php
        $isOperator = auth()->check() && (is_object(auth()->user()->role) ? auth()->user()->role->value : auth()->user()->role) === 'OPERATOR';
        $dashRoute = $isOperator ? route('operator.dashboard') : route('admin.dashboard');
        $roleSubtitle = $isOperator ? 'Logistics Dispatch Operations' : 'Control Tower Management';
        $accentColor = $isOperator ? 'teal' : 'indigo';
    @endphp

    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col shadow-xl flex-shrink-0">
        <div class="p-6 border-b border-slate-800">
            <a href="{{ $dashRoute }}" class="font-black text-2xl tracking-tight text-white flex items-center gap-2">
                CargoTrace<span class="text-{{ $accentColor }}-400">.</span>
            </a>
            <span class="text-[11px] font-semibold text-slate-400 block mt-0.5">{{ $roleSubtitle }}</span>
        </div>
        <nav class="flex-grow p-4 space-y-1 overflow-y-auto">
            <a href="{{ $dashRoute }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-bold text-xs {{ request()->routeIs('*.dashboard') ? ($isOperator ? 'bg-teal-600 text-white' : 'bg-indigo-600 text-white') : 'hover:bg-slate-800 text-slate-300' }} transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>
            
            <div class="pt-4 pb-2 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Operations</div>
            @if($isOperator)
                <a href="{{ route('operator.shipments.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-bold text-xs {{ request()->routeIs('operator.shipments.create') ? 'bg-teal-600 text-white' : 'hover:bg-slate-800 text-slate-300' }} transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Pengiriman Baru
                </a>
            @else
                <a href="{{ route('admin.shipments.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-bold text-xs {{ request()->routeIs('admin.shipments.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }} transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Shipments
                </a>
                <a href="{{ route('admin.audit_logs.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-bold text-xs {{ request()->routeIs('admin.audit_logs.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }} transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Audit Trail
                </a>

                <div class="pt-4 pb-2 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Master Data</div>
                <a href="{{ route('admin.carriers.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-bold text-xs {{ request()->routeIs('admin.carriers.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }} transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    Carriers
                </a>
                <a href="{{ route('admin.locations.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-bold text-xs {{ request()->routeIs('admin.locations.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }} transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                    Locations & Hubs
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-bold text-xs {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }} transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    User Accounts
                </a>
            @endif

            <div class="pt-4 pb-2 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Public View</div>
            <a href="{{ route('track.index') }}" target="_blank" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-800 text-slate-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                Live Tracking Page &nearr;
            </a>
        </nav>
        
        <!-- Bottom Logout -->
        <div class="p-4 border-t border-slate-800">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-rose-900/60 text-slate-300 hover:text-rose-200 transition font-bold text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-screen overflow-y-auto">
        <!-- Topbar -->
        <header class="bg-white shadow-sm border-b border-slate-200 px-8 py-4 flex justify-between items-center sticky top-0 z-30">
            <h2 class="text-lg font-bold text-slate-800">@yield('header_title', 'CargoTrace')</h2>
            <div class="flex items-center gap-4">
                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold uppercase">
                    {{ is_object(auth()->user()->role) ? auth()->user()->role->value : auth()->user()->role }}
                </span>
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="text-xs font-bold text-slate-700">{{ auth()->user()->name }}</span>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="p-6 md:p-8 flex-grow">
            <!-- Flash Session Alerts -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

</body>
</html>
