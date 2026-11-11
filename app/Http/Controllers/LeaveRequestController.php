<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    // Menampilkan form pengajuan izin
    public function create()
    {
        $user = Auth::user();
        
        // Proteksi: Hanya Siswa (Role 5) dan Orang Tua (Role 6) yang boleh buka halaman ini
        if (!in_array($user->role_id, [5, 6])) {
            return redirect('/dashboard')->with('error', 'Akses ditolak. Hanya Siswa dan Orang Tua yang dapat mengajukan izin.');
        }

        $students = $user->role_id == 6
            ? \App\Models\User::query()->whereIn('id', $user->linkedStudentIds())->orderBy('fullname')->get()
            : collect();

        return view('leave_requests.create', compact('user', 'students'));
    }

    // Memproses data yang dikirim dari form
    public function store(Request $request)
    {
        // 1. Validasi Input & File
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string',
            'document'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // Maksimal 2MB
            'student_id' => 'required_if:role_id,6|nullable|integer',
        ], [
            'document.mimes' => 'Format file harus berupa PDF, JPG, JPEG, atau PNG.',
            'document.max'   => 'Ukuran file maksimal adalah 2MB.',
            'end_date.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal mulai.'
        ]);

        $user = Auth::user();
        
        // 2. Menentukan ID Siswa
        // Jika yang login Siswa, id-nya adalah id-nya sendiri.
        // Jika Orang Tua, id siswanya diambil dari parent_id yang diisi saat registrasi tadi.
        $studentId = $user->role_id == 5 ? $user->id : $request->input('student_id');
        if ($user->role_id == 6) {
            $linkedStudentIds = $user->linkedStudentIds();
            $studentId ??= $linkedStudentIds->count() === 1 ? $linkedStudentIds->first() : null;
            abort_unless($studentId && $linkedStudentIds->contains((int) $studentId), 403, 'Pilih siswa yang terhubung ke akun Anda.');
        }

        // 3. Proses Upload File ke folder storage/app/public/leave_documents
        $documentPath = $request->file('document')->store('leave_documents', 'public');

        // 4. Simpan Data ke Database
        LeaveRequest::create([
            'student_id'      => $studentId,
            'applicant_id'    => $user->id,
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date,
            'reason'          => $request->reason,
            'document_path'   => $documentPath,
            'status_approval' => 'Pending',
        ]);

        return redirect('/dashboard')->with('success', 'Pengajuan izin berhasil dikirim dan sedang menunggu persetujuan Wali Kelas atau Guru BK.');
    }

    // Menampilkan daftar pengajuan izin untuk Wali Kelas / BK
    public function index()
    {
        $user = Auth::user();
        
        // Proteksi: Hanya Wali Kelas (3) dan Tim Kesiswaan (4) yang boleh melihat halaman ini
        if (!in_array($user->role_id, [3, 4])) {
            return redirect('/dashboard')->with('error', 'Akses ditolak. Halaman khusus Wali Kelas / BK.');
        }

        // Tarik data izin, urutkan dari yang paling baru
        $query = LeaveRequest::with(['student.classe', 'applicant'])->orderBy('created_at', 'desc');

        // Jika dia Wali Kelas, filter HANYA data izin dari siswa di kelasnya
        if ($user->role_id == 3 && $user->class_id) {
            $query->whereHas('student', function($q) use ($user) {
                $q->where('class_id', $user->class_id);
            });
        }

        $leaveRequests = $query->get();

        return view('leave_requests.index', compact('leaveRequests'));
    }

    // Memproses persetujuan (Approve/Reject)
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array($user->role_id, [3, 4])) {
            abort(403, 'Akses Ditolak');
        }

        $request->validate([
            'status_approval' => 'required|in:Approved,Rejected'
        ]);

        $leave = LeaveRequest::findOrFail($id);
        $leave->update([
            'status_approval' => $request->status_approval,
            'verified_by'     => $user->id
        ]);

        $statusText = $request->status_approval == 'Approved' ? 'Disetujui' : 'Ditolak';
        return redirect()->back()->with('success', "Pengajuan izin berhasil $statusText!");
    }
}