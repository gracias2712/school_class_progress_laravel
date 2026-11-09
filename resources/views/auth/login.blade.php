<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMK N 1 Bantul</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg border border-slate-100">
        <div class="flex flex-col items-center mb-6">
            <img src="{{ asset('images/logo_sekolah.png') }}" alt="Logo SMK N 1 Bantul" class="w-20 h-20 mb-3 object-contain">
            <h2 class="text-2xl font-extrabold text-center text-slate-800">SMK N 1 Bantul</h2>
        </div>

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
                <div class="relative">
                    <input type="password" name="password" id="login-password" required
                        class="w-full px-4 py-3 pr-12 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="button" id="toggle-login-password" aria-label="Tampilkan password" aria-pressed="false"
                        class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-500 hover:text-slate-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6-9.75-6-9.75-6Z" />
                            <circle cx="12" cy="12" r="2.5" />
                        </svg>
                    </button>
                </div>
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
    <script>
        const passwordInput = document.getElementById('login-password');
        const passwordToggle = document.getElementById('toggle-login-password');

        passwordToggle.addEventListener('click', () => {
            const showPassword = passwordInput.type === 'password';
            passwordInput.type = showPassword ? 'text' : 'password';
            passwordToggle.setAttribute('aria-pressed', String(showPassword));
            passwordToggle.setAttribute('aria-label', showPassword ? 'Sembunyikan password' : 'Tampilkan password');
        });
    </script>
</body>
</html>