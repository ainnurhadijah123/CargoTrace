<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - GoTrace')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Smooth transitions for sidebar width and label visibility */
        .sidebar-transition {
            transition: width 0.25s ease-in-out, transform 0.25s ease-in-out;
        }
        /* Mini/Collapsed mode styles when .sidebar-mini is applied */
        .sidebar-mini .nav-label,
        .sidebar-mini .nav-section-title,
        .sidebar-mini .sidebar-brand-text,
        .sidebar-mini .sidebar-subtitle {
            display: none !important;
        }
        .sidebar-mini .nav-section-divider {
            display: block !important;
        }
        .sidebar-mini .nav-item {
            justify-content: center;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        .sidebar-mini .sidebar-brand-container {
            justify-content: center;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        /* Tooltip behavior only when sidebar is in mini/collapsed icon mode */
        .sidebar-mini .nav-item:hover .nav-tooltip {
            display: block;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden font-sans relative selection:bg-indigo-600 selection:text-white">

    @php
        $isOperator = auth()->check() && (is_object(auth()->user()->role) ? auth()->user()->role->value : auth()->user()->role) === 'OPERATOR';
        $dashRoute = $isOperator ? route('operator.dashboard') : route('admin.dashboard');
        $roleSubtitle = $isOperator ? 'Logistics Dispatch Operations' : 'Control Tower Management';
        $accentColor = $isOperator ? 'teal' : 'indigo';
    @endphp

    <!-- Mobile Backdrop Overlay -->
    <div id="sidebarBackdrop" onclick="closeMobileSidebar()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 hidden md:hidden transition-opacity"></div>

    <!-- Sidebar Navigation -->
    <aside id="mainSidebar" class="sidebar-transition fixed md:static inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col shadow-2xl md:shadow-xl flex-shrink-0 -translate-x-full md:translate-x-0">
        
        <!-- Sidebar Header / Brand -->
        <div class="p-4 sm:p-5 border-b border-slate-800 flex items-center justify-between sidebar-brand-container">
            <a href="{{ $dashRoute }}" class="flex items-center gap-2 group">
                <div class="w-9 h-9 rounded-xl bg-{{ $accentColor }}-600 text-white flex items-center justify-center font-black text-base shadow-md group-hover:scale-105 transition flex-shrink-0">
                    GT
                </div>
                <div class="sidebar-brand-text min-w-0">
                    <span class="font-black text-xl tracking-tight text-white flex items-center leading-none">
                        GoTrace<span class="text-{{ $accentColor }}-400">.</span>
                    </span>
                    <span class="sidebar-subtitle text-[10px] font-semibold text-slate-400 block mt-1 truncate">{{ $roleSubtitle }}</span>
                </div>
            </a>
            
            <!-- Mobile Close Button -->
            <button type="button" onclick="closeMobileSidebar()" class="md:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none" title="Tutup Menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-grow p-3 space-y-1 overflow-y-auto overflow-x-hidden">
            <!-- Dashboard Link -->
            <a href="{{ $dashRoute }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold text-xs {{ request()->routeIs('*.dashboard') ? ($isOperator ? 'bg-teal-600 text-white shadow-sm' : 'bg-indigo-600 text-white shadow-sm') : 'hover:bg-slate-800 text-slate-300' }} transition relative group" title="Dashboard">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="nav-label truncate">Dashboard</span>
                <span class="nav-tooltip hidden fixed md:absolute left-16 md:left-full ml-2 px-2.5 py-1 bg-slate-950 text-white text-[11px] font-semibold rounded-lg shadow-xl whitespace-nowrap z-50 pointer-events-none">Dashboard</span>
            </a>
            
            <!-- Operations Group -->
            <div class="nav-section-title pt-3 pb-1.5 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Operations</div>
            <div class="nav-section-divider hidden border-t border-slate-800 my-2"></div>

            @if($isOperator)
                <a href="{{ route('operator.shipments.create') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold text-xs {{ request()->routeIs('operator.shipments.create') ? 'bg-teal-600 text-white' : 'hover:bg-slate-800 text-slate-300' }} transition relative group" title="Buat Pengiriman Baru">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span class="nav-label truncate">Buat Pengiriman Baru</span>
                    <span class="nav-tooltip hidden fixed md:absolute left-16 md:left-full ml-2 px-2.5 py-1 bg-slate-950 text-white text-[11px] font-semibold rounded-lg shadow-xl whitespace-nowrap z-50 pointer-events-none">Buat Pengiriman</span>
                </a>
            @else
                <a href="{{ route('admin.shipments.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold text-xs {{ request()->routeIs('admin.shipments.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }} transition relative group" title="Shipments">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span class="nav-label truncate">Shipments</span>
                    <span class="nav-tooltip hidden fixed md:absolute left-16 md:left-full ml-2 px-2.5 py-1 bg-slate-950 text-white text-[11px] font-semibold rounded-lg shadow-xl whitespace-nowrap z-50 pointer-events-none">Shipments</span>
                </a>
                <a href="{{ route('admin.audit_logs.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold text-xs {{ request()->routeIs('admin.audit_logs.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }} transition relative group" title="Audit Trail">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="nav-label truncate">Audit Trail</span>
                    <span class="nav-tooltip hidden fixed md:absolute left-16 md:left-full ml-2 px-2.5 py-1 bg-slate-950 text-white text-[11px] font-semibold rounded-lg shadow-xl whitespace-nowrap z-50 pointer-events-none">Audit Trail</span>
                </a>

                <!-- Master Data Group -->
                <div class="nav-section-title pt-3 pb-1.5 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Master Data</div>
                <div class="nav-section-divider hidden border-t border-slate-800 my-2"></div>

                <a href="{{ route('admin.carriers.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold text-xs {{ request()->routeIs('admin.carriers.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }} transition relative group" title="Carriers">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    <span class="nav-label truncate">Carriers</span>
                    <span class="nav-tooltip hidden fixed md:absolute left-16 md:left-full ml-2 px-2.5 py-1 bg-slate-950 text-white text-[11px] font-semibold rounded-lg shadow-xl whitespace-nowrap z-50 pointer-events-none">Carriers</span>
                </a>
                <a href="{{ route('admin.locations.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold text-xs {{ request()->routeIs('admin.locations.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }} transition relative group" title="Locations & Hubs">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                    <span class="nav-label truncate">Locations & Hubs</span>
                    <span class="nav-tooltip hidden fixed md:absolute left-16 md:left-full ml-2 px-2.5 py-1 bg-slate-950 text-white text-[11px] font-semibold rounded-lg shadow-xl whitespace-nowrap z-50 pointer-events-none">Locations</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold text-xs {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }} transition relative group" title="User Accounts">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="nav-label truncate">User Accounts</span>
                    <span class="nav-tooltip hidden fixed md:absolute left-16 md:left-full ml-2 px-2.5 py-1 bg-slate-950 text-white text-[11px] font-semibold rounded-lg shadow-xl whitespace-nowrap z-50 pointer-events-none">User Accounts</span>
                </a>

                <!-- Developer & API Group -->
                <div class="nav-section-title pt-3 pb-1.5 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Developer & API</div>
                <div class="nav-section-divider hidden border-t border-slate-800 my-2"></div>

                <a href="{{ route('api.docs') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold text-xs {{ request()->routeIs('api.docs*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }} transition relative group" title="REST API Docs">
                    <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    <span class="nav-label truncate">REST API Docs</span>
                    <span class="nav-tooltip hidden fixed md:absolute left-16 md:left-full ml-2 px-2.5 py-1 bg-slate-950 text-white text-[11px] font-semibold rounded-lg shadow-xl whitespace-nowrap z-50 pointer-events-none">REST API Docs</span>
                </a>
            @endif

            <!-- Public View -->
            <div class="nav-section-title pt-3 pb-1.5 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Public View</div>
            <div class="nav-section-divider hidden border-t border-slate-800 my-2"></div>

            <a href="{{ route('track.index') }}" target="_blank" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-800 text-slate-400 hover:text-white transition relative group" title="Live Tracking Portal">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                <span class="nav-label truncate">Portal Pelacakan &nearr;</span>
                <span class="nav-tooltip hidden fixed md:absolute left-16 md:left-full ml-2 px-2.5 py-1 bg-slate-950 text-white text-[11px] font-semibold rounded-lg shadow-xl whitespace-nowrap z-50 pointer-events-none">Portal Publik</span>
            </a>
        </nav>
        
        <!-- Bottom Logout -->
        <div class="p-3 border-t border-slate-800">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item flex items-center justify-center gap-3 w-full px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-rose-900/60 text-slate-300 hover:text-rose-200 transition font-bold text-xs group relative" title="Sign Out">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span class="nav-label truncate">Sign Out</span>
                    <span class="nav-tooltip hidden fixed md:absolute left-16 md:left-full ml-2 px-2.5 py-1 bg-slate-950 text-white text-[11px] font-semibold rounded-lg shadow-xl whitespace-nowrap z-50 pointer-events-none">Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-screen overflow-y-auto min-w-0">
        <!-- Topbar -->
        <header class="bg-white shadow-sm border-b border-slate-200 px-4 sm:px-8 py-3.5 flex justify-between items-center sticky top-0 z-30">
            <div class="flex items-center gap-3 min-w-0">
                <!-- Sidebar Toggle Button (Desktop: Mini/Expanded, Mobile: Drawer) -->
                <button id="sidebarToggleBtn" type="button" onclick="toggleSidebar()" class="p-2 -ml-1 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 active:scale-95 focus:outline-none transition" title="Sembunyikan / Buka Sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h2 class="text-base sm:text-lg font-bold text-slate-800 truncate">@yield('header_title', 'GoTrace')</h2>
            </div>

            <div class="flex items-center gap-2 sm:gap-4 flex-shrink-0">
                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] sm:text-xs font-bold uppercase tracking-wider">
                    {{ is_object(auth()->user()->role) ? auth()->user()->role->value : auth()->user()->role }}
                </span>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="text-xs font-bold text-slate-700 hidden sm:inline max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="p-4 sm:p-6 md:p-8 flex-grow">
            <!-- Flash Session Alerts -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Sidebar Responsive Toggle Script -->
    <script>
        const sidebar = document.getElementById('mainSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        // Check stored desktop preference
        const isDesktopMini = localStorage.getItem('gotrace_sidebar_mini') === 'true';
        if (window.innerWidth >= 768 && isDesktopMini) {
            applyMiniSidebar(true);
        }

        function toggleSidebar() {
            if (window.innerWidth < 768) {
                // Mobile behavior: slide in/out drawer
                const isOpen = !sidebar.classList.contains('-translate-x-full');
                if (isOpen) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            } else {
                // Desktop behavior: toggle expanded (w-64) vs mini icon mode (w-20)
                const isMini = sidebar.classList.contains('sidebar-mini');
                applyMiniSidebar(!isMini);
                localStorage.setItem('gotrace_sidebar_mini', (!isMini).toString());
            }
        }

        function applyMiniSidebar(mini) {
            if (mini) {
                sidebar.classList.add('sidebar-mini');
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20');
            } else {
                sidebar.classList.remove('sidebar-mini');
                sidebar.classList.remove('w-20');
                sidebar.classList.add('w-64');
            }
        }

        function openMobileSidebar() {
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeMobileSidebar() {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Auto close mobile drawer on screen resize if enlarged
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                backdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                sidebar.classList.remove('-translate-x-full');
                const isMini = localStorage.getItem('gotrace_sidebar_mini') === 'true';
                applyMiniSidebar(isMini);
            } else {
                sidebar.classList.remove('sidebar-mini', 'w-20');
                sidebar.classList.add('w-64');
                closeMobileSidebar();
            }
        });
    </script>
</body>
</html>
