<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // Mengambil daftar siswa berdasarkan ID Kelas
    public function getStudents(Request $request)
    {
        $request->validate(['class_id' => 'required|integer']);

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
        $request->validate([
            'schedule_id' => 'required|integer',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|integer',
            'attendances.*.status' => 'required|in:Hadir,Sakit,Izin,Alpa'
        ]);

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