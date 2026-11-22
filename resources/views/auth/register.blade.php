<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - CargoTrace Global Visibility</title>
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

        <!-- Glassmorphism Registration Card -->
        <div class="bg-white/80 backdrop-blur-2xl border border-white/90 rounded-3xl shadow-[0_20px_50px_rgba(42,75,94,0.12)] p-8 sm:p-9 text-slate-800">
            
            <!-- Brand & Header -->
            <div class="text-center mb-7">
                <div class="inline-flex items-center gap-2.5 mb-2">
                    <span class="p-2 rounded-xl bg-[#0c1a24] text-white shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </span>
                    <span class="text-2xl sm:text-3xl font-black text-[#0b1723] tracking-tight">CargoTrace<span class="text-[#2c566e]">.</span></span>
                </div>
                <p class="text-xs font-medium text-slate-500">Registrasi Akun Pelanggan Baru</p>
                <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/70 border border-white/90 text-[10px] font-mono font-bold text-[#1e3c4f] shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]"></span>
                    <span>NEW CUSTOMER REGISTRATION</span>
                </div>
            </div>

            <!-- Error Notification -->
            @if($errors->any())
                <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1 shadow-sm">
                    <div class="font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>Terjadi kesalahan input:</span>
                    </div>
                    <ul class="list-disc list-inside pl-1 text-[11px] text-rose-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Registration Form -->
            <form action="{{ route('register.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 font-mono">Nama Lengkap</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="contoh: Suhaila Azmi"
                            class="w-full pl-10 pr-4 py-3 bg-[#f0f5f9] hover:bg-[#e8f0f6] focus:bg-white border border-slate-200 focus:border-[#2c566e] focus:ring-4 focus:ring-[#2c566e]/10 rounded-2xl text-slate-800 text-sm font-medium transition placeholder:text-slate-400 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 font-mono">Alamat Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                        </div>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@domain.com"
                            class="w-full pl-10 pr-4 py-3 bg-[#f0f5f9] hover:bg-[#e8f0f6] focus:bg-white border border-slate-200 focus:border-[#2c566e] focus:ring-4 focus:ring-[#2c566e]/10 rounded-2xl text-slate-800 text-sm font-medium transition placeholder:text-slate-400 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 font-mono">Kata Sandi (Min. 8 karakter)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full pl-10 pr-4 py-3 bg-[#f0f5f9] hover:bg-[#e8f0f6] focus:bg-white border border-slate-200 focus:border-[#2c566e] focus:ring-4 focus:ring-[#2c566e]/10 rounded-2xl text-slate-800 text-sm font-medium transition placeholder:text-slate-400 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 font-mono">Konfirmasi Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <input type="password" name="password_confirmation" required placeholder="••••••••"
                            class="w-full pl-10 pr-4 py-3 bg-[#f0f5f9] hover:bg-[#e8f0f6] focus:bg-white border border-slate-200 focus:border-[#2c566e] focus:ring-4 focus:ring-[#2c566e]/10 rounded-2xl text-slate-800 text-sm font-medium transition placeholder:text-slate-400 focus:outline-none">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                        class="w-full py-3.5 bg-[#0c1a24] hover:bg-[#1a3142] active:scale-[0.99] font-bold rounded-2xl text-white transition duration-200 shadow-lg shadow-slate-900/15 flex items-center justify-center gap-2 text-sm">
                        <span>Buat Akun Pelanggan</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>
            
            <p class="mt-6 text-center text-xs text-slate-500 font-medium">
                Sudah memiliki akun? 
                <a href="{{ route('login') }}" class="font-bold text-[#1e3c4f] hover:text-[#0b1723] hover:underline transition-colors">Masuk di sini</a>
            </p>

        </div>
    </div>
</body>
</html>
