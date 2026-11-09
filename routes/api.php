<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\JournalController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\AttendanceReportController;
use App\Http\Controllers\Api\AttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

Route::post('deploy-migrate', function (Request $request) {
    $expectedSecret = config('app.deploy_secret');
    if (! is_string($expectedSecret) || $expectedSecret === '') {
        return response()->json(['status' => 'error', 'message' => 'Endpoint migrasi belum dikonfigurasi.'], 503);
    }

    if (! hash_equals($expectedSecret, (string) $request->bearerToken())) {
        return response()->json(['status' => 'error', 'message' => 'Akses ditolak: token salah.'], 403);
    }

    try {
        // 1. Jalankan migrasi database
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = trim(Artisan::output());

        // 2. Jalankan seeder (jika parameter ?seed=1 disertakan)
        $seedOutput = 'Seeding dilewati.';
        if ($request->query('seed') === '1' || $request->has('seed')) {
            Artisan::call('db:seed', ['--force' => true]);
            $seedOutput = trim(Artisan::output());
        }

        // 3. Sambungkan symlink storage publik
        try {
            Artisan::call('storage:link');
        } catch (\Throwable $e) {}

        // 4. Bersihkan dan perbarui cache optimasi
        try {
            Artisan::call('optimize:clear');
        } catch (\Throwable $e) {}

        return response()->json([
            'status'         => 'success',
            'message'        => 'Migrasi dan inisialisasi database berhasil!',
            'migrate_output' => $migrateOutput,
            'seed_output'    => $seedOutput,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Gagal migrasi: ' . $e->getMessage(),
        ], 500);
    }
    })->middleware('throttle:3,1');


// --- RUTE PUBLIK (TIDAK PERLU LOGIN) ---
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/registration/classes', [AuthController::class, 'registrationClasses']);

// Rute Lupa Password OTP
Route::post('/forgot-password/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword']);

// --- RUTE TERPROTEKSI (WAJIB LOGIN) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/children', [AuthController::class, 'children']);

    Route::get('/schedules', [ScheduleController::class, 'index']);
    
    // Endpoint API untuk Jurnal Mobile
    Route::post('/journals', [JournalController::class, 'store']);

    // Route untuk Verifikasi Izin (Wali Kelas)
    Route::get('/leave-requests/pending', [LeaveRequestController::class, 'pending']);
    Route::post('/leave-requests/{id}/approve', [LeaveRequestController::class, 'approve']);
    Route::post('/leave-requests/{id}/reject', [LeaveRequestController::class, 'reject']);

    // Route untuk Laporan Presensi (Wali Kelas & Guru BK)
    Route::get('/attendances/report', [AttendanceReportController::class, 'report']);
    Route::get('/attendances/report/export', [AttendanceReportController::class, 'export']); // ROUTE BARU UNTUK EXPORT
    Route::get('/classes', [AttendanceReportController::class, 'getClasses']); // Rute baru untuk dropdown BK

    // Route untuk Presensi Siswa (Guru Mapel & Wali Kelas)
    Route::get('/students', [AttendanceController::class, 'getStudents']);
    Route::post('/attendances', [AttendanceController::class, 'store']);

    Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    Route::get('/leave-requests/mine', [LeaveRequestController::class, 'mine']);
    Route::post('/change-password', [\App\Http\Controllers\Api\AuthController::class, 'changePassword'])
    ->name('api.change-password');
});