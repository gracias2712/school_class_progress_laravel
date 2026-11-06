<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Class Progress</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg border border-slate-100">
        <h2 class="text-2xl font-extrabold text-center text-slate-800 mb-6">Login SCP</h2>

        @if(session('success'))
            <div class="mb-5 p-3 bg-emerald-50 text-emerald-700 text-sm rounded-xl font-bold text-center border border-emerald-200">
                ✅ {{ session('success') }}
            </div>
        @endif

        @error('username')
            <div class="mb-5 p-3 bg-red-50 text-red-700 text-sm rounded-xl font-bold text-center border border-red-200">
                ❌ {{ $message }}
            </div>
        @enderror

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-slate-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" required
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-slate-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-center justify-between pb-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm font-bold text-slate-600">Ingat Saya</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-sm font-bold text-blue-600 hover:text-blue-800">Lupa Password?</a>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3.5 px-4 rounded-xl hover:bg-blue-700 transition shadow-md">
                Masuk
            </button>
        </form>

        <div class="mt-6 text-center text-sm font-medium text-slate-500">
            Belum punya akun? <a href="{{ route('register') }}" class="text-blue-600 font-bold hover:underline">Daftar Waka</a>
        </div>
    </div>
</body>
</html>