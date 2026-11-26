<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - CargoTrace Global Visibility</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-[#e8edf1] text-slate-800 min-h-screen flex items-center justify-center p-4 relative overflow-x-hidden selection:bg-slate-800 selection:text-white">

    <!-- Authentic Frosted Ice & Slate Swirl Background (from Design Mockup) -->
    <div class="absolute inset-0 pointer-events-none select-none z-0 overflow-hidden">
        <img src="{{ asset('images/landing_hero_bg.jpg') }}" alt="CargoTrace Hero Background" class="w-full h-full object-cover object-center" />
    </div>

    <!-- Soft Ambient Frosted Veil -->
    <div class="absolute inset-0 bg-[#e8edf1]/40 backdrop-blur-[3px] pointer-events-none z-0"></div>

    <div class="relative z-10 w-full max-w-md my-8">
        
        <!-- Back to Tracking Portal Button -->
        <div class="mb-4">
            <a href="{{ route('track.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-[#0b1723] bg-white/70 hover:bg-white px-4 py-2 rounded-full border border-white/90 shadow-sm backdrop-blur transition duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Portal Pelacakan</span>
            </a>
        </div>

        <!-- Glassmorphism Login Card -->
        <div class="bg-white/80 backdrop-blur-2xl border border-white/90 rounded-3xl shadow-[0_20px_50px_rgba(42,75,94,0.12)] p-8 sm:p-9 text-slate-800">
            
            <!-- Brand & Header -->
            <div class="text-center mb-7">
                <div class="inline-flex items-center gap-2.5 mb-2">
                    <span class="p-2 rounded-xl bg-[#0c1a24] text-white shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </span>
                    <span class="text-2xl sm:text-3xl font-black text-[#0b1723] tracking-tight">CargoTrace<span class="text-[#2c566e]">.</span></span>
                </div>
                <p class="text-xs font-medium text-slate-500">Global Logistics Visibility & Telemetry Management</p>
                <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/70 border border-white/90 text-[10px] font-mono font-bold text-[#1e3c4f] shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#2a6d85] animate-ping"></span>
                    <span>SECURE ACCESS GATEWAY</span>
                </div>
            </div>

            @php
                $intendedResi = request('resi') ?? session('intended_tracking_resi');
            @endphp

            <!-- Protected Cargo Security Alert -->
            @if($intendedResi || session('warning'))
                <div class="mb-5 p-4 rounded-2xl bg-amber-50/90 border border-amber-200 text-amber-900 text-xs shadow-sm">
                    <div class="flex items-center gap-2 font-bold text-amber-950 mb-1">
                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Akses Kargo Terlindungi @if($intendedResi)• <span class="font-mono font-black text-amber-950">{{ $intendedResi }}</span>@endif</span>
                    </div>
                    <p class="text-amber-800 leading-relaxed">
                        {{ session('warning') ?? 'Rincian kargo ini bersifat privat. Silakan masuk sebagai pemilik pesanan terkait atau staf logistik untuk melihat status dan spesifikasi pengiriman.' }}
                    </p>
                </div>
            @endif

            <!-- Error Notification -->
            @if($errors->any())
                <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium flex items-center gap-2.5 shadow-sm">
                    <svg class="w-4 h-4 flex-shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
                @csrf
                @if($intendedResi)
                    <input type="hidden" name="intended_resi" value="{{ $intendedResi }}">
                @endif
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 font-mono">Alamat Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@cargotrace.com"
                            class="w-full pl-10 pr-4 py-3 bg-[#f0f5f9] hover:bg-[#e8f0f6] focus:bg-white border border-slate-200 focus:border-[#2c566e] focus:ring-4 focus:ring-[#2c566e]/10 rounded-2xl text-slate-800 text-sm font-medium transition placeholder:text-slate-400 focus:outline-none">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider font-mono">Kata Sandi</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input type="password" id="password" name="password" required placeholder="••••••••"
                            class="w-full pl-10 pr-10 py-3 bg-[#f0f5f9] hover:bg-[#e8f0f6] focus:bg-white border border-slate-200 focus:border-[#2c566e] focus:ring-4 focus:ring-[#2c566e]/10 rounded-2xl text-slate-800 text-sm font-medium transition placeholder:text-slate-400 focus:outline-none">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                            <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                        class="w-full py-3.5 bg-[#0c1a24] hover:bg-[#1a3142] active:scale-[0.99] font-bold rounded-2xl text-white transition duration-200 shadow-lg shadow-slate-900/15 flex items-center justify-center gap-2 text-sm">
                        <span>Masuk ke Akun</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>
            
            <p class="mt-6 text-center text-xs text-slate-500 font-medium">
                Belum punya akun? 
                <a href="{{ route('register') }}" class="font-bold text-[#1e3c4f] hover:text-[#0b1723] hover:underline transition-colors">Buat akun baru</a>
            </p>

        </div>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>';
            } else {
                pwd.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            }
        }
    </script>
</body>
</html>
