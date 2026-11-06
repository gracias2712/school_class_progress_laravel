<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - School Class Progress</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center py-10 font-sans text-slate-800">

    <div class="bg-white p-8 md:p-10 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 w-full max-w-md">
        
        <div class="text-center mb-8">
            <h2 class="text-2xl font-extrabold text-blue-600 mb-2">Pendaftaran Akun Web</h2>
            <p class="text-sm text-slate-500 font-medium">Khusus akses panel Bapak/Ibu Waka Kurikulum</p>
        </div>

        @if($errors->any())
            <div class="mb-5 p-4 bg-red-50 text-red-700 text-sm font-bold rounded-xl border border-red-100">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.process') }}" method="POST" class="space-y-5">
            @csrf
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap</label>
                <input type="text" name="fullname" value="{{ old('fullname') }}" required placeholder="Contoh: Budi Santoso"
                    class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm font-medium">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" required placeholder="Contoh: wakakur"
                    class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm font-medium">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Alamat Email Aktif <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="Contoh: wakakur@sekolah.com" 
                    class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition font-medium">
                @error('email') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                <p class="text-[11px] text-slate-400 mt-1.5 font-medium">Email ini akan digunakan jika Anda lupa password.</p>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="password" required minlength="6" placeholder="Minimal 6 karakter"
                        class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm font-medium">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-4 flex items-center text-slate-400 hover:text-blue-600 transition">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Mendaftar Sebagai</label>
                <select name="role_id" required class="w-full px-4 py-3.5 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm font-extrabold cursor-pointer text-center">
                    
                    <!-- KITA HARDCODE ROLE ID 1 DI SINI AGAR TIDAK MUNGKIN KOSONG -->
                    <option value="1" selected>WAKA KURIKULUM</option>
                    
                </select>
                <p class="text-[11px] text-slate-400 mt-2 text-center font-medium">Siswa, Guru, dan Orang Tua registrasi melalui Aplikasi Mobile.</p>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3.5 px-4 rounded-xl hover:bg-blue-700 transition shadow-md mt-2">
                Daftar Sekarang
            </button>
        </form>
        
        <p class="text-center text-sm font-medium text-slate-500 mt-6">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-600 font-bold hover:underline">Login di sini</a>
        </p>
    </div>

    <!-- Script Mata Password -->
    <script>
        function togglePassword() {
            let pwd = document.getElementById('password');
            let icon = document.getElementById('eyeIcon');
            
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />';
            } else {
                pwd.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />';
            }
        }
    </script>
</body>
</html>