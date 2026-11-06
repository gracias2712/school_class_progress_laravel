<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Journal;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class JournalController extends Controller
{
    public function index()
    {
        // Menampilkan jadwal khusus untuk guru yang sedang login
        $schedules = Schedule::where('teacher_id', Auth::id())
            ->with(['classe', 'room'])
            ->get();

        return view('guru.journals.index', compact('schedules'));
    }

    public function create($scheduleId)
    {
        // Memuat form input jurnal dan data siswa di kelas tersebut
        $schedule = Schedule::with(['classe.students', 'room'])->findOrFail($scheduleId);
        
        return view('guru.journals.create', compact('schedule'));
    }

    public function store(Request $request, $scheduleId)
    {
        $request->validate([
            'material'    => 'required|string|max:255',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'attendances' => 'required|array',
        ]);

        $schedule = Schedule::findOrFail($scheduleId);
        $today = Carbon::today()->toDateString();

        // Mencegah Guru menginput jurnal 2x di hari dan jadwal yang sama
        if (Journal::where('schedule_id', $schedule->id)->where('date', $today)->exists()) {
            return redirect()->route('guru.journals.index')->with('error', 'Gagal! Jurnal untuk jadwal ini sudah diisi hari ini.');
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('journals', 'public');
        }

        // Menyimpan Jurnal
        $journal = Journal::create([
            'schedule_id'       => $schedule->id,
            'date'              => $today,
            'topic_description' => $request->material,
            'photo_path'        => $photoPath,
        ]);

        // LOGIKA AUTO-SYNC PERIZINAN SISWA
        if ($request->has('attendances')) {
            foreach ($request->attendances as $studentId => $status) {
                
                // Cek izin aktif (Approved) untuk siswa pada hari ini
                $activeLeave = LeaveRequest::where('student_id', $studentId)
                    ->where('status_approval', 'Approved')
                    ->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today)
                    ->first();

                // Jika ada izin resmi, paksa (override) status presensinya menjadi Izin
                if ($activeLeave) {
                    $finalStatus = 'Izin'; 
                } else {
                    $finalStatus = $status; // Gunakan input dari form guru
                }

                Attendance::create([
                    'journal_id' => $journal->id,
                    'student_id' => $studentId,
                    'status'     => $finalStatus,
                    'date'       => $today,
                ]);
            }
        }

        return redirect()->route('guru.journals.index')->with('success', 'Jurnal dan presensi berhasil disimpan. (Data perizinan tersinkronisasi otomatis)');
    }

    public function history($scheduleId)
    {
        // Melihat riwayat jurnal untuk satu jadwal spesifik
        $schedule = Schedule::with(['classe', 'room'])->findOrFail($scheduleId);
        
        $journals = Journal::where('schedule_id', $scheduleId)
            ->with(['attendances.student'])
            ->latest('date')
            ->get();

        return view('guru.journals.history', compact('schedule', 'journals'));
    }
}