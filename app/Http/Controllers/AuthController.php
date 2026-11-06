<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Journal;
use App\Models\SystemConfiguration;
use App\Models\Role;
use App\Models\Classe;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showRegister()
    {
        // Mengambil semua role kecuali Waka Kurikulum (Role ID 1) agar tidak ada yang iseng mendaftar jadi Waka
        $roles = Role::where('id', '!=', 1)->get(); 
        $classes = Classe::all();
        // Mengambil data siswa untuk dropdown Orang Tua
        $students = User::whereHas('role', function($q){
            $q->where('role_name', 'Siswa');
        })->get();

        return view('auth.register', compact('roles', 'classes', 'students'));
    }

    public function processRegister(Request $request)
    {
        $request->validate([
            'fullname'  => 'required|string|max:255',
            'username'  => 'required|string|max:50|unique:users',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'role_id'   => 'required|exists:roles,id',
            'class_id'  => 'nullable|required_if:role_id,3,5|exists:classes,id',
            'parent_id' => 'nullable|required_if:role_id,6|exists:users,id',
        ]);

        User::create([
            'fullname'  => $request->fullname,
            'username'  => $request->username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role_id'   => $request->role_id,
            'class_id'  => in_array($request->role_id, [3, 5]) ? $request->class_id : null,
            'parent_id' => $request->role_id == 6 ? $request->parent_id : null,
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login dengan username Anda.');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // Cek apakah checkbox "Ingat Saya" dicentang
        $remember = $request->has('remember');

        // Autentikasi dengan memasukkan variabel $remember
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function dashboard()
    {
        $user = Auth::user();
        
        // Ambil konfigurasi sistem global
        $sysConfig = SystemConfiguration::first() ?? new SystemConfiguration([
            'active_cycle' => '1',
            'is_schedule_locked' => false
        ]);
        
        $rawActiveCycle = $sysConfig->active_cycle ?? '1';
        $activeCycleClean = str_replace('Putaran ', '', $rawActiveCycle);
        $activeCycle = $activeCycleClean; 
        
        // Dapatkan status kunci jadwal (boolean)
        $isLocked = ($sysConfig->is_schedule_locked == 1 || $sysConfig->is_schedule_locked == true || $sysConfig->is_schedule_locked === '1' || $sysConfig->is_schedule_locked === 'true');

        $alerts = [];
        $schedulesToday = [];
        $stats = []; // Variabel penampung statistik

        // LOGIKA KHUSUS WAKA KURIKULUM (Role 1)
        if ($user && $user->role_id == 1) {
            // Hitung total data langsung dari database
            $stats['students'] = User::where('role_id', 5)->count();
            // Gabungan Guru Pengampu (2), Wali Kelas (3), dan BK (4)
            $stats['teachers'] = User::whereIn('role_id', [2, 3, 4])->count(); 
            $stats['classes']  = DB::table('classes')->count();
            $stats['rooms']    = DB::table('rooms')->count();

            $siswaCount   = $stats['students'];
            $guruCount    = $stats['teachers'];
            $kelasCount   = $stats['classes'];
            $ruanganCount = $stats['rooms'];
        } else {
            $siswaCount = 0; $guruCount = 0; $kelasCount = 0; $ruanganCount = 0;
        }

        // LOGIKA KHUSUS GURU PENGAMPU (Role 2)
        if ($user && $user->role_id == 2) {
            $now = Carbon::now('Asia/Jakarta');
            $today = $now->format('Y-m-d');
            
            $englishDay = $now->format('l');
            $daysMap = [
                'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
            ];
            $indoDay = $daysMap[$englishDay] ?? 'Kamis';

            $possibleDays = [$englishDay, $indoDay];
            $possibleCycles = [$activeCycleClean, 'Putaran ' . $activeCycleClean];
            $currentTime = $now->format('H:i:s');

            $schedulesToday = Schedule::with(['classe', 'room'])
                ->where('teacher_id', $user->id)
                ->whereIn('day_of_week', $possibleDays)
                ->whereIn('cycle_type', $possibleCycles)
                ->get();

            foreach ($schedulesToday as $schedule) {
                $hasJournal = Journal::where('schedule_id', $schedule->id)
                    ->whereDate('date', $today)
                    ->exists();

                if (!$hasJournal) {
                    $endTimePlus30 = Carbon::parse($schedule->end_time, 'Asia/Jakarta')->addMinutes(30)->format('H:i:s');
                    if ($currentTime > $endTimePlus30) {
                        $alerts[] = [
                            'schedule_id' => $schedule->id,
                            'subject'     => $schedule->subject,
                            'class_name'  => optional($schedule->classe)->class_name ?? '-',
                            'end_time'    => $schedule->end_time,
                            'overdue_min' => round(Carbon::parse($schedule->end_time, 'Asia/Jakarta')->diffInMinutes($now)),
                        ];
                    }
                }
            }
        }

        // Lempar semua variabel ke tampilan dashboard
        return view('dashboard', compact(
            'user', 
            'sysConfig', 
            'activeCycle', 
            'alerts', 
            'schedulesToday', 
            'stats', 
            'isLocked', 
            'siswaCount', 
            'guruCount', 
            'kelasCount', 
            'ruanganCount'
        ));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}