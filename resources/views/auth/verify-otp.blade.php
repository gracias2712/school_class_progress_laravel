<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - SCP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Menghilangkan panah (spinners) pada input number */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
    </style>
</head>
<body class="bg-slate-100 flex items-center justify-center h-screen font-sans">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg border border-slate-200">
        <h2 class="text-2xl font-extrabold text-slate-800 mb-2 text-center">Verifikasi OTP 🛡️</h2>
        <p class="text-sm text-slate-500 text-center mb-6">Silakan cek tabel <b>password_otps</b> di phpMyAdmin untuk melihat 6 digit angka ajaib Anda.</p>
        
        @if(session('success')) <div class="mb-4 p-3 bg-emerald-50 text-emerald-700 text-sm rounded-lg border border-emerald-200 text-center font-bold">✅ {{ session('success') }}</div> @endif
        @error('otp') <div class="mb-4 p-3 bg-red-50 text-red-600 text-sm rounded-lg border border-red-200 text-center font-semibold">❌ {{ $message }}</div> @enderror
        
        <form action="{{ route('password.otp.verify') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <div>
                <label class="block text-slate-700 text-sm font-bold mb-1 text-center">Kode 6 Digit OTP</label>
                <input type="number" name="otp" required autofocus placeholder="• • • • • •" class="w-full px-4 py-4 text-center text-2xl tracking-[0.5em] border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 transition font-bold text-slate-700">
            </div>
            <button type="submit" class="w-full mt-4 bg-blue-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-blue-700 transition shadow-md">
                Verifikasi Kode
            </button>
        </form>
    </div>
</body>
</html>