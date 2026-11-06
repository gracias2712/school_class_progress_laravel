<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SystemConfigController;
use App\Http\Controllers\Guru\JournalController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ForgotPasswordController;

// Route Halaman Utama
Route::get('/', function () {
    return redirect('/login');
});

// Route Registrasi Web (Khusus Waka Kurikulum)
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'processRegister'])->name('register.process');

// Route Login (Hanya untuk tamu/yang belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Rute Lupa Password (OTP) - Diletakkan di dalam guest agar bisa diakses sebelum login
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('password.email');

    Route::get('/verify-otp', [ForgotPasswordController::class, 'showOtpForm'])->name('password.otp.form');
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.otp.verify');

    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

// Route yang membutuhkan autentikasi (Login)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Route::resource otomatis sudah mencakup edit dan update
    Route::resource('schedules', ScheduleController::class);
    Route::post('schedules/import', [\App\Http\Controllers\ScheduleController::class, 'import'])->name('schedules.import');

    Route::post('/config/toggle-cycle', [SystemConfigController::class, 'toggleCycle'])->name('config.toggle-cycle');
    Route::post('/config/toggle-lock', [SystemConfigController::class, 'toggleLock'])->name('config.toggle-lock');

    Route::get('/guru/journals', [JournalController::class, 'index'])->name('guru.journals.index');
    Route::get('/guru/journals/create/{scheduleId}', [JournalController::class, 'create'])->name('guru.journals.create');
    Route::post('/guru/journals/store/{scheduleId}', [JournalController::class, 'store'])->name('guru.journals.store');
    Route::get('/guru/journals/history/{scheduleId}', [JournalController::class, 'history'])->name('guru.journals.history');

    Route::get('/leave/create', [LeaveRequestController::class, 'create'])->name('leave.create');
    Route::post('/leave/store', [LeaveRequestController::class, 'store'])->name('leave.store');
    Route::get('/leave', [LeaveRequestController::class, 'index'])->name('leave.index');
    Route::patch('/leave/{id}/status', [LeaveRequestController::class, 'updateStatus'])->name('leave.updateStatus');

    // Manajemen Master Data (Khusus Waka)
    Route::resource('master/classes', \App\Http\Controllers\Master\ClassController::class)->except(['create', 'show', 'edit', 'update']);
    Route::post('master/classes/import', [\App\Http\Controllers\Master\ClassController::class, 'import'])->name('classes.import');

    Route::resource('master/rooms', \App\Http\Controllers\Master\RoomController::class)->except(['create', 'show', 'edit', 'update']);
    Route::post('master/rooms/import', [\App\Http\Controllers\Master\RoomController::class, 'import'])->name('rooms.import');
    
    // --- Manajemen Master Data Pengguna (Dipecah per Role) ---
    Route::prefix('master/users')->name('users.')->group(function () {
        // Halaman Index per Role
        Route::get('/guru', [\App\Http\Controllers\Master\UserController::class, 'indexGuru'])->name('guru');
        Route::get('/walikelas', [\App\Http\Controllers\Master\UserController::class, 'indexWaliKelas'])->name('walikelas');
        Route::get('/bk', [\App\Http\Controllers\Master\UserController::class, 'indexBK'])->name('bk');
        Route::get('/siswa', [\App\Http\Controllers\Master\UserController::class, 'indexSiswa'])->name('siswa');
        Route::get('/orangtua', [\App\Http\Controllers\Master\UserController::class, 'indexOrangTua'])->name('orangtua');

        // CRUD Global (Dipakai bersama dari semua halaman)
        Route::post('/store', [\App\Http\Controllers\Master\UserController::class, 'store'])->name('store');
        Route::put('/{id}/update', [\App\Http\Controllers\Master\UserController::class, 'update'])->name('update');
        Route::delete('/{id}/destroy', [\App\Http\Controllers\Master\UserController::class, 'destroy'])->name('destroy');

        // Route Import Excel
        Route::post('/import-pegawai', [\App\Http\Controllers\Master\UserController::class, 'importPegawai'])->name('import.pegawai');
        Route::post('/import-siswa', [\App\Http\Controllers\Master\UserController::class, 'importSiswa'])->name('import.siswa');
    });

        Route::get('histories/cycles', [\App\Http\Controllers\CycleHistoryController::class, 'index'])->name('histories.cycles');
        Route::get('master/journals', [\App\Http\Controllers\Master\WakaJournalController::class, 'index'])->name('waka.journals');
        Route::get('master/attendance-reports', [\App\Http\Controllers\Master\AttendanceReportController::class, 'index'])->name('waka.attendance-reports');
    
        Route::post('/waka/toggle-cycle', [ScheduleController::class, 'toggleCycle'])->name('waka.toggleCycle');
        Route::post('/waka/toggle-lock', [ScheduleController::class, 'toggleLock'])->name('waka.toggleLock');
});