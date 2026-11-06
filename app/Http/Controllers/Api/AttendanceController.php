<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    use AuthorizesApiRoles;

    // Mengambil daftar siswa berdasarkan ID Kelas
    public function getStudents(Request $request)
    {
        $request->validate(['class_id' => 'required|integer']);
        $user = $this->authorizeRoles($request, ['Guru', 'Wali Kelas']);
        abort_unless(
            Schedule::where('teacher_id', $user->id)
                ->where('class_id', $request->class_id)
                ->exists(),
            403,
            'Anda tidak mengajar kelas tersebut.'
        );

        // Mengambil User yang memiliki role 'Siswa'
        $students = User::where('class_id', $request->class_id)
            ->whereHas('role', function($query) {
                $query->where('role_name', 'Siswa')->orWhere('role_name', 'siswa');
            })
            ->orderBy('fullname', 'asc')
            ->get(['id', 'fullname']);

        return response()->json(['success' => true, 'data' => $students]);
    }

    // Menyimpan presensi massal
    public function store(Request $request)
    {
        $user = $this->authorizeRoles($request, ['Guru', 'Wali Kelas']);
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|integer',
            'attendances.*.status' => 'required|in:Hadir,Sakit,Izin,Alpa'
        ]);

        $schedule = Schedule::findOrFail($request->schedule_id);
        abort_unless(
            (int) $schedule->teacher_id === (int) $user->id,
            403,
            'Jadwal tersebut bukan milik akun Anda.'
        );

        $studentIds = collect($request->attendances)->pluck('student_id')->unique();
        $validStudentCount = User::whereIn('id', $studentIds)
            ->where('class_id', $schedule->class_id)
            ->whereHas('role', fn ($query) => $query->where('role_name', 'Siswa'))
            ->count();
        abort_unless(
            $validStudentCount === $studentIds->count(),
            422,
            'Daftar siswa tidak sesuai dengan kelas pada jadwal.'
        );

        $today = Carbon::today()->toDateString();

        // Cari ID Jurnal terlebih dahulu berdasarkan jadwal & hari ini
        $journal = DB::table('journals')
            ->where('schedule_id', $request->schedule_id)
            ->whereDate('date', $today)
            ->first();

        // Cegah pengisian absen jika guru belum membuat jurnal
        if (!$journal) {
            return response()->json([
                'success' => false, 
                'message' => 'Jurnal belum dibuat! Silakan isi Jurnal hari ini terlebih dahulu.'
            ], 404);
        }

        DB::beginTransaction();
        try {
            foreach ($request->attendances as $att) {
                DB::table('attendances')->updateOrInsert(
                    [
                        'journal_id'  => $journal->id,
                        'student_id'  => $att['student_id'],
                    ],
                    [
                        'status'      => $att['status'],
                        'date'        => $today,
                        'created_at'  => Carbon::now(),
                        'updated_at'  => Carbon::now(),
                    ]
                );
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data presensi berhasil disimpan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan presensi: ' . $e->getMessage()], 500);
        }
    }
}