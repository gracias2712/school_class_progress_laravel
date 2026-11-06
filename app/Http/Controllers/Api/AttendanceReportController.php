<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Classe;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceReportController extends Controller
{
    public function getClasses()
    {
        $classes = Classe::orderBy('class_name')->pluck('class_name');
        return response()->json(['success' => true, 'data' => $classes]);
    }

    public function report(Request $request)
    {
        $user = $request->user();
        
        $query = User::whereHas('role', function($q) { 
            $q->where('role_name', 'Siswa'); 
        });

        if ($request->filled('class_name') && $request->class_name !== 'Semua Kelas') {
            $query->whereHas('classe', function($q) use ($request) {
                $q->where('class_name', $request->class_name);
            });
        } elseif ($user && $user->class_id) { 
            $query->where('class_id', $user->class_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                  ->orWhere('nisn_nip', 'like', "%{$search}%");
            });
        }

        $students = $query->get();
        $studentIds = $students->pluck('id');

        $attendanceQuery = Attendance::whereIn('student_id', $studentIds);
        if ($request->filter_tanggal === 'hari_ini') {
            $attendanceQuery->whereDate('date', Carbon::today()->toDateString());
        }
        $allAttendances = $attendanceQuery->get();

        $reportData = $students->map(function($student) use ($allAttendances) {
            $studentAttendances = $allAttendances->where('student_id', $student->id);
            return [
                'nama_siswa' => $student->fullname,
                'nisn'       => $student->nisn_nip ?? '-',
                'hadir'      => $studentAttendances->where('status', 'Hadir')->count(),
                'izin'       => $studentAttendances->where('status', 'Izin')->count(),
                'sakit'      => $studentAttendances->where('status', 'Sakit')->count(),
                'alpha'      => $studentAttendances->whereIn('status', ['Alpa', 'Alpha'])->count(),
            ];
        });

        return response()->json(['success' => true, 'data' => $reportData]);
    }

    public function export(Request $request)
    {
        $format = $request->query('format', 'pdf');
        return response()->json(['message' => "Fungsi export $format berhasil dipanggil."]);
    }
}