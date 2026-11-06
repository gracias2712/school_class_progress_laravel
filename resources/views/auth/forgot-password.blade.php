<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SCP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg border border-slate-100">
        
        <h2 class="text-2xl font-extrabold text-center text-slate-800 mb-2">Lupa Password? 🔒</h2>
        <p class="text-sm text-slate-500 text-center mb-6 font-medium">
            Masukkan Username dan Email Anda untuk menerima OTP.
        </p>

        @if($errors->any())
            <div class="mb-5 p-3 bg-red-50 text-red-700 text-sm rounded-xl font-bold text-center border border-red-200">
                ❌ {{ $errors->first() }}
            </div>
        @endif
        
        @if(session('success'))
            <div class="mb-5 p-3 bg-emerald-50 text-emerald-700 text-sm rounded-xl font-bold text-center border border-emerald-200">
                ✅ {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-slate-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username" required placeholder="Contoh: waka1"
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-slate-700 text-sm font-bold mb-2">Email Terdaftar</label>
                <input type="email" name="email" required placeholder="email@gmail.com"
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <button type="submit" class="w-full bg-[#0f172a] text-white font-bold py-3.5 px-4 rounded-xl hover:bg-slate-800 transition shadow-md mt-2">
                Kirim Kode OTP
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 text-sm font-bold flex items-center justify-center gap-2">
                &larr; Kembali ke Login
            </a>
        </div>
    </div>
</body>
</html>