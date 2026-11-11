<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Classe;
use App\Mail\SendOtpMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Api\AuthorizesApiRoles;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    use AuthorizesApiRoles;

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);

        $user = User::query()->where('username', $validated['username'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Username atau password salah.'], 401);
        }

        $token = $user->createToken($validated['device_name'], ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->userData($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['role', 'classe']);
        return response()->json(['data' => $this->userData($user)]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $this->authorizeRoles($request, [
            'Guru',
            'Wali Kelas',
            'Guru BK',
            'Siswa',
            'Orang Tua',
        ]);

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->update([
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => $this->userData($user->load(['role', 'classe'])),
        ]);
    }

    public function children(Request $request): JsonResponse
    {
        $user = $this->authorizeRoles($request, ['Orang Tua']);

        $students = User::query()
            ->with('classe:id,class_name')
            ->whereIn('id', $user->linkedStudentIds())
            ->whereHas('role', fn ($query) => $query->where('role_name', 'Siswa'))
            ->orderBy('fullname')
            ->get(['id', 'fullname', 'class_id']);

        return response()->json([
            'success' => true,
            'data' => $students->map(fn (User $student) => [
                'id' => $student->id,
                'fullname' => $student->fullname,
                'class_name' => $student->classe?->class_name,
            ])->values(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil.']);
    }

    // =========================================================================
    // FITUR REGISTRASI & LUPA PASSWORD (API)
    // =========================================================================

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'role'     => 'required|string|in:Siswa',
            'class_id' => 'required|integer|exists:classes,id',
        ]);

        $role = Role::where('role_name', $validated['role'])->first();
        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Role tidak valid.'], 400);
        }

        User::create([
            'fullname' => $validated['fullname'],
            'email'    => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role_id'  => $role->id,
            'class_id' => $validated['class_id'],
        ]);

        return response()->json(['success' => true, 'message' => 'Registrasi berhasil! Silakan login.']);
    }

    public function registrationClasses(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Classe::query()
                ->orderBy('class_name')
                ->get(['id', 'class_name']),
        ]);
    }

    public function sendOtp(Request $request): JsonResponse
    {
        // Validasi input mewajibkan username dan email
        $request->validate([
            'username' => 'required|string',
            'email'    => 'required|email'
        ]);

        // 1. Cek apakah username ada di database
        $userByUsername = User::where('username', $request->username)->first();
        if (!$userByUsername) {
            return response()->json(['success' => false, 'message' => 'Username tidak terdaftar di sistem.'], 404);
        }

        // 2. Cek apakah kombinasi username dan email cocok
        $user = User::where('username', $request->username)->where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Email tidak cocok dengan username tersebut.'], 400);
        }

        $otpCode = rand(100000, 999999);

        DB::table('password_otps')->updateOrInsert(
            ['email' => $user->email],
            ['otp' => $otpCode, 'created_at' => Carbon::now()]
        );

        Mail::to($user->email)->send(new SendOtpMail($otpCode, $user->fullname));

        return response()->json(['success' => true, 'message' => 'Kode OTP telah dikirim ke email Anda.']);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email', 'otp' => 'required|numeric']);

        $otpRecord = DB::table('password_otps')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$otpRecord) {
            return response()->json(['success' => false, 'message' => 'Kode OTP salah atau tidak valid.'], 400);
        }

        if (Carbon::parse($otpRecord->created_at)->addMinutes(15)->isPast()) {
            return response()->json(['success' => false, 'message' => 'Kode OTP telah kedaluwarsa.'], 400);
        }

        return response()->json(['success' => true, 'message' => 'OTP Valid. Silakan masukkan password baru.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email', 'password' => 'required|min:6']);

        $user = User::where('email', $request->email)->first();
        if($user) {
            $user->update(['password' => Hash::make($request->password)]);
            DB::table('password_otps')->where('email', $request->email)->delete();
            return response()->json(['success' => true, 'message' => 'Password berhasil diubah.']);
        }
        
        return response()->json(['success' => false, 'message' => 'Terjadi kesalahan.'], 400);
    }

    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'fullname' => $user->fullname,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role?->role_name,
            'class' => $user->classe?->class_name,
        ];
    }

    // Fungsi untuk Ganti Password saat user sudah login
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        $user = $request->user();

        // Cek apakah password lama yang diinputkan benar
        if (!\Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false, 
                'message' => 'Password lama tidak sesuai.'
            ], 400);
        }

        // Simpan password baru
        $user->update([
            'password' => \Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Password berhasil diubah!'
        ]);
    }
}