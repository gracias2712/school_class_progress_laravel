<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password Baru - SCP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center h-screen font-sans">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg border border-slate-200">
        <h2 class="text-2xl font-extrabold text-slate-800 mb-2 text-center">Password Baru ✨</h2>
        <p class="text-sm text-slate-500 text-center mb-6">Silakan buat kata sandi baru yang kuat untuk akun Anda.</p>
        
        <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="otp" value="{{ $otp }}">
            
            <!-- Input Password Baru -->
            <div class="relative">
                <label class="block text-slate-700 text-sm font-bold mb-1">Password Baru</label>
                <input type="password" name="password" id="pass1" required minlength="6" inputmode="text" autocomplete="new-password" autocapitalize="off" spellcheck="false" placeholder="Minimal 6 karakter" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 transition pr-12">
                <button type="button" onclick="togglePass('pass1')" class="absolute right-3 top-9 text-slate-400 hover:text-slate-600">👁️</button>
            </div>

            <!-- Input Ulangi Password -->
            <div class="relative">
                <label class="block text-slate-700 text-sm font-bold mb-1">Ulangi Password Baru</label>
                <input type="password" name="password_confirmation" id="pass2" required minlength="6" inputmode="text" autocomplete="new-password" autocapitalize="off" spellcheck="false" placeholder="Ulangi password baru" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 transition pr-12">
                <button type="button" onclick="togglePass('pass2')" class="absolute right-3 top-9 text-slate-400 hover:text-slate-600">👁️</button>
            </div>

            @error('password') <p class="text-red-500 text-xs font-semibold mt-1">❌ {{ $message }}</p> @enderror
            
            <button type="submit" class="w-full mt-6 bg-emerald-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-emerald-700 transition shadow-md">
                Simpan & Login
            </button>
        </form>
    </div>

    <!-- Script untuk Toggle Fitur Mata -->
    <script>
        function togglePass(id) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
            } else {
                input.type = "password";
            }
        }
    </script>
</body>
</html>