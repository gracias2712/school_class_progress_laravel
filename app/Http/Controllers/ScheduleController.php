<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\SystemConfiguration;
use App\Models\User;
use App\Models\Classe; 
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ScheduleImport;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        // Proteksi: Hanya Waka (1) dan Guru (2) (Asumsi: Waka=1, Guru=2. Sesuaikan jika Wali Kelas memiliki ID berbeda dan perlu mengakses halaman ini)
        if (!in_array(Auth::user()->role_id, [1, 2])) {
            return redirect('/dashboard')->with('error', 'Akses ditolak.');
        }

        $config = SystemConfiguration::first() ?? new SystemConfiguration(['active_cycle' => '1', 'is_schedule_locked' => false]);
        $globalCycle = str_replace('Putaran ', '', $config->active_cycle);
        
        // Ambil filter siklus dari URL (Dropdown), jika tidak ada gunakan siklus global
        $selectedCycle = str_replace('Putaran ', '', $request->get('cycle_type', $globalCycle));

        // Jika Guru (2), hanya lihat jadwalnya sendiri. Jika Waka (1), lihat semua.
        $query = Schedule::with(['teacher', 'classe', 'room'])
            ->where(function($q) use ($selectedCycle) {
                $q->where('cycle_type', $selectedCycle)
                  ->orWhere('cycle_type', 'Putaran ' . $selectedCycle);
            });

        if (Auth::user()->role_id == 2) {
            $query->where('teacher_id', Auth::id());
        }

        $schedules = $query->orderBy('day_of_week')->orderBy('start_time')->get();

        // --- PERBAIKAN SOLUSI 1 MULAI DI SINI ---
        // Panggil semua user yang Role-nya mengandung kata 'Guru' ATAU 'Wali Kelas'
        $teachers = User::whereHas('role', function($query) {
            $query->where('role_name', 'like', '%Guru%')
                  ->orWhere('role_name', 'like', '%Wali%');
        })->orderBy('fullname')->get();
        // --- PERBAIKAN SOLUSI 1 SELESAI DI SINI ---

        $classes  = Classe::orderBy('class_name')->get(); 
        $rooms    = Room::orderBy('room_name')->get();

        return view('schedules.index', compact('schedules', 'config', 'selectedCycle', 'teachers', 'classes', 'rooms'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role_id != 1) return redirect('/dashboard')->with('error', 'Akses Ditolak!');

        $config = SystemConfiguration::first();
        if ($config && $config->is_schedule_locked) {
            return redirect()->back()->with('error', 'Gagal! Transaksi jadwal telah DIKUNCI oleh Waka Kurikulum.');
        }

        // Validasi
        $request->validate([
            'teacher_id'  => 'required|exists:users,id',
            'class_id'    => 'required|exists:classes,id',
            'room_id'     => 'required|exists:rooms,id',
            'subject'     => 'required|string|max:100', // Pastikan input ini tidak 'readonly' di view blade-nya
            'day_of_week' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'cycle_type'  => 'required|in:1,2,Putaran 1,Putaran 2',
        ], [
            'end_time.after' => 'Jam selesai pelajaran harus lebih besar/akhir daripada jam mulai.'
        ]);

        $cleanCycleType = str_replace('Putaran ', '', $request->cycle_type);

        // Algoritma Cek Bentrok
        $conflict = Schedule::where('day_of_week', $request->day_of_week)
            ->where(function($q) use ($cleanCycleType) {
                $q->where('cycle_type', $cleanCycleType)->orWhere('cycle_type', 'Putaran ' . $cleanCycleType);
            })
            ->where(function ($query) use ($request) {
                $query->where('room_id', $request->room_id)
                      ->orWhere('class_id', $request->class_id)
                      ->orWhere('teacher_id', $request->teacher_id);
            })
            ->where('start_time', '<', $request->end_time)
            ->where('end_time', '>', $request->start_time)
            ->first();

        if ($conflict) {
            $reason = '';
            if ($conflict->room_id == $request->room_id) $reason = 'Ruangan ' . optional($conflict->room)->room_name;
            elseif ($conflict->class_id == $request->class_id) $reason = 'Kelas ' . optional($conflict->classe)->class_name;
            else $reason = 'Guru ' . optional($conflict->teacher)->fullname;

            return redirect()->back()->withInput()->with('error', "Jadwal Bentrok! {$reason} sudah terpakai pada jam {$conflict->start_time} - {$conflict->end_time}.");
        }

        Schedule::create([
            'teacher_id' => $request->teacher_id, 'class_id' => $request->class_id,
            'room_id' => $request->room_id, 'subject' => $request->subject,
            'day_of_week' => $request->day_of_week, 'start_time' => $request->start_time,
            'end_time' => $request->end_time, 'cycle_type' => $cleanCycleType,
        ]);

        return redirect()->route('schedules.index', ['cycle_type' => $cleanCycleType])
            ->with('success', 'Jadwal pelajaran berhasil ditambahkan tanpa bentrok!');
    }

    public function destroy($id)
    {
        if (Auth::user()->role_id != 1) return redirect('/dashboard');
        
        $config = SystemConfiguration::first();
        if ($config && $config->is_schedule_locked) {
            return redirect()->back()->with('error', 'Gagal! Jadwal sedang dikunci.');
        }

        // KODE PERBAIKAN: Hapus jurnal dan presensi yang menempel pada jadwal ini (Cascading Delete)
        $journals = \App\Models\Journal::where('schedule_id', $id)->get();
        foreach ($journals as $journal) {
            // Hapus presensi dulu
            \App\Models\Attendance::where('journal_id', $journal->id)->delete();
            // Baru hapus jurnal
            $journal->delete();
        }

        Schedule::findOrFail($id)->delete();
        
        return redirect()->back()->with('success', 'Jadwal, Jurnal, dan Presensi terkait berhasil dihapus secara permanen!');
    }

    public function toggleLock(Request $request)
    {
        $config = SystemConfiguration::first() ?? new SystemConfiguration();
        $config->is_schedule_locked = !$config->is_schedule_locked;
        $config->updated_by = Auth::id() ?? 1;
        $config->save();

        $statusText = $config->is_schedule_locked ? 'DIKUNCI' : 'DIBUKA';
        return redirect()->back()->with('success', "Status keamanan jadwal berhasil {$statusText}.");
    }

    public function toggleCycle(Request $request)
    {
        $request->validate([
            'active_cycle' => 'required|in:1,2'
        ]);

        $config = SystemConfiguration::first() ?? new SystemConfiguration();
        
        $oldCycle = str_replace('Putaran ', '', $config->active_cycle ?? '1');
        $newCycle = $request->active_cycle;

        $config->active_cycle = $newCycle;
        $config->updated_by = Auth::id() ?? 1;
        $config->save();

        // CATAT KE DALAM HISTORY PUTARAN
        \App\Models\CycleHistory::create([
            'changed_by_user_id' => Auth::id(),
            'from_cycle' => $oldCycle,
            'to_cycle' => $newCycle,
            'change_reason' => 'Pergantian Siklus Dasbor'
        ]);

        return redirect()->back()->with('success', "Siklus blok aktif berhasil diubah ke Putaran {$newCycle}.");
    }

    public function edit($id)
    {
        $isLocked = DB::table('system_configurations')->value('is_schedule_locked');
        if ($isLocked) {
            return redirect()->back()->with('error', 'Sistem terkunci! Tidak bisa mengedit jadwal.');
        }

        $schedule = Schedule::findOrFail($id);
        
        // --- PERBAIKAN SOLUSI 1 DI FORM EDIT ---
        $users = User::whereHas('role', function($query) {
            $query->where('role_name', 'like', '%Guru%')
                  ->orWhere('role_name', 'like', '%Wali%');
        })->orderBy('fullname')->get();
        // --- PERBAIKAN SOLUSI 1 SELESAI ---
        
        $classes = Classe::all();
        $rooms = Room::all();

        return view('schedules.edit', compact('schedule', 'users', 'classes', 'rooms'));
    }

    public function update(Request $request, $id)
    {
        $isLocked = DB::table('system_configurations')->value('is_schedule_locked');
        if ($isLocked) {
            return redirect()->back()->with('error', 'Sistem terkunci!');
        }

        $request->validate([
            'teacher_id' => 'required',
            'class_id' => 'required',
            'room_id' => 'required',
            'subject' => 'required',
            'day_of_week' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'cycle_type' => 'required',
        ]);

        $cleanCycleType = str_replace('Putaran ', '', $request->cycle_type);

        // LOGIKA ANTI-BENTROK
        $konflik = Schedule::where('id', '!=', $id)
            ->where(function($q) use ($cleanCycleType) {
                $q->where('cycle_type', $cleanCycleType)->orWhere('cycle_type', 'Putaran ' . $cleanCycleType);
            })
            ->where('day_of_week', $request->day_of_week)
            ->where(function ($query) use ($request) {
                $query->where('room_id', $request->room_id)
                      ->orWhere('class_id', $request->class_id)
                      ->orWhere('teacher_id', $request->teacher_id);
            })
            ->whereTime('start_time', '<', $request->end_time)
            ->whereTime('end_time', '>', $request->start_time)
            ->exists();

        if ($konflik) {
            return redirect()->back()->withInput()->with('error', 'Gagal Edit! Terjadi bentrok jadwal (Ruang/Kelas/Waktu/Guru).');
        }

        $schedule = Schedule::findOrFail($id);
        $request->merge(['cycle_type' => $cleanCycleType]);
        $schedule->update($request->all());

        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function import(Request $request)
    {
        if (Auth::user()->role_id != 1) return redirect('/dashboard')->with('error', 'Akses Ditolak!');

        $config = SystemConfiguration::first();
        if ($config && $config->is_schedule_locked) {
            return redirect()->back()->with('error', 'Gagal! Transaksi jadwal telah DIKUNCI oleh Waka Kurikulum.');
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new ScheduleImport, $request->file('file'));
            return redirect()->back()->with('success', 'Jadwal berhasil diimport! Baris yang bentrok atau tidak ditemukan ID-nya dilewati secara otomatis.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import jadwal: ' . $e->getMessage());
        }
    }
}