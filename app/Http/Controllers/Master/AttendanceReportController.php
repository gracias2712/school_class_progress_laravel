<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil data kelas untuk menu Dropdown Filter
        $classes = DB::table('classes')->get();

        // 2. Tangkap nilai filter dari URL (jika ada)
        $classId = $request->class_id;
        $month = $request->month ?? date('Y-m'); // Default bulan ini (contoh: 2026-10)

        // 3. Ambil data Siswa (HANYA ROLE SISWA = 5)
        $query = DB::table('users')
            ->whereNotNull('class_id')
            ->where('role_id', 5);
        
        if ($classId) {
            $query->where('class_id', $classId);
        }
        
        $students = $query->select('id', 'fullname', 'nisn_nip', 'class_id')->get();

        // 4. Ambil data SELURUH Status Kehadiran pada bulan yang dipilih
        $attendances = DB::table('attendances')
            ->where('created_at', 'like', $month . '%')
            ->get();

        // 5. Gabungkan hitungan absen ke data siswa
        foreach ($students as $student) {
            $studentAttendances = $attendances->where('student_id', $student->id); 
            
            $student->hadir = $studentAttendances->where('status', 'Hadir')->count();
            $student->sakit = $studentAttendances->where('status', 'Sakit')->count();
            $student->izin = $studentAttendances->where('status', 'Izin')->count();
            $student->alpha = $studentAttendances->whereIn('status', ['Alpha', 'Alpa'])->count(); 
            
            $student->total_absen = $student->sakit + $student->izin + $student->alpha;
            
            $className = $classes->where('id', $student->class_id)->first();
            $student->class_name = $className ? $className->class_name : '-';
        }

        return view('master.attendance.index', compact('classes', 'students', 'classId', 'month'));
    }
}