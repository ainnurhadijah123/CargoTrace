<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - CargoTrace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-slate-800 border border-slate-700 rounded-xl shadow-xl p-8 my-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-blue-400">CargoTrace</h1>
            <p class="text-sm text-slate-400 mt-2">Buat akun baru untuk melacak kargo Anda</p>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.submit') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-blue-500 text-slate-100 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required 
                    class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-blue-500 text-slate-100 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Password (Minimal 8 karakter)</label>
                <input type="password" name="password" required 
                    class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-blue-500 text-slate-100 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required 
                    class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-blue-500 text-slate-100 text-sm">
            </div>

            <button type="submit" 
                class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 font-semibold rounded-lg text-white transition duration-200 shadow-lg shadow-blue-600/20">
                Daftar Sekarang
            </button>
        </form>

        <div class="mt-5 text-center text-sm text-slate-400">
            Sudah punya akun? 
            <a href="{{ route('login') }}" class="font-medium text-blue-400 hover:text-blue-300 transition-colors">Masuk di sini</a>
        </div>
    </div>
</body>
</html>