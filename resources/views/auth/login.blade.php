<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CargoTrace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-slate-800 border border-slate-700 rounded-xl shadow-xl p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-blue-400">CargoTrace</h1>
            <p class="text-sm text-slate-400 mt-2">Silakan masuk ke akun Anda</p>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-blue-500 text-slate-100 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Password</label>
                <input type="password" name="password" required 
                    class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-blue-500 text-slate-100 text-sm">
            </div>

            <button type="submit" 
                class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 font-semibold rounded-lg text-white transition duration-200 shadow-lg shadow-blue-600/20">
                Masuk
            </button>
        </form>
        
        <p class="mt-5 text-center text-sm text-slate-400">
            Belum punya akun? 
            <a href="{{ route('register') }}" class="font-medium text-blue-400 hover:text-blue-300 transition-colors">Buat akun baru</a>
        </p>

    </div>
</body>
</html>