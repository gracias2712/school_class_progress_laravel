<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    // 1. Tampilkan form Lupa Password
    public function showRequestForm() {
        return view('auth.forgot-password');
    }

    // 2. Buat OTP, kirim via Gmail SMTP, & simpan ke Database
    public function sendOtp(Request $request) {
        $request->validate([
            'username' => 'required',
            'email'    => 'required|email'
        ]);
        
        $user = User::where('username', $request->username)
                    ->where('email', $request->email)
                    ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Kombinasi Username dan Email tidak ditemukan di sistem.']);
        }

        // Generate 6 digit angka
        $otp = rand(100000, 999999); 
        
        // Hapus OTP lama dan simpan yang baru
        DB::table('password_otps')->where('email', $request->email)->delete();
        DB::table('password_otps')->insert([
            'email'      => $request->email,
            'otp'        => $otp,
            'created_at' => Carbon::now()
        ]);

        // Eksekusi pengiriman Email OTP ke Gmail
        try {
            Mail::to($request->email)->send(new SendOtpMail($otp, $user->fullname));
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Gagal mengirim email OTP. Pastikan koneksi internet & setelan SMTP Gmail benar.']);
        }

        return redirect()->route('password.otp.form', ['email' => $request->email])
                         ->with('success', 'Kode OTP Berhasil Dikirim! Silakan cek Kotak Masuk atau Spam di Email Anda.');
    }

    // 3. Tampilkan form validasi OTP
    public function showOtpForm(Request $request) {
        if (!$request->email) return redirect()->route('login');
        return view('auth.verify-otp', ['email' => $request->email]);
    }

    // 4. Periksa kecocokan OTP
    public function verifyOtp(Request $request) {
        $request->validate(['email' => 'required', 'otp' => 'required|numeric']);

        $valid = DB::table('password_otps')->where('email', $request->email)->where('otp', $request->otp)->first();
        if (!$valid) {
            return back()->withErrors(['otp' => 'Kode OTP salah atau sudah kadaluarsa.']);
        }

        return redirect()->route('password.reset.form', ['email' => $request->email, 'otp' => $request->otp]);
    }

    // 5. Tampilkan form pembuatan password baru
    public function showResetForm(Request $request) {
        if (!$request->email || !$request->otp) return redirect()->route('login');
        return view('auth.reset-password', ['email' => $request->email, 'otp' => $request->otp]);
    }

    // 6. Timpa password lama dengan yang baru
    public function resetPassword(Request $request) {
        $request->validate([
            'email'    => 'required',
            'password' => 'required|min:6|confirmed' 
        ]);

        // Update password baru di database
        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);
        
        // Hapus jejak OTP
        DB::table('password_otps')->where('email', $request->email)->delete();

        return redirect('/login')->with('success', 'Password berhasil diubah! Silakan login dengan password baru.');
    }
}