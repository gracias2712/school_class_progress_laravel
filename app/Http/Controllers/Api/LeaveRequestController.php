<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    use AuthorizesApiRoles;

    public function index(Request $request)
    {
        $user = $this->authorizeRoles($request, ['Wali Kelas', 'Guru BK']);

        $query = LeaveRequest::with(['student', 'applicant'])
            ->orderByDesc('created_at');

        if ($user->role?->role_name === 'Wali Kelas') {
            abort_unless($user->class_id, 403, 'Akun Wali Kelas belum terhubung ke kelas.');
            $query->whereHas('student', fn ($students) => $students->where('class_id', $user->class_id));
        }

        $data = $query->get()->map(function ($req) {
            $studentUser = $req->student ?? $req->applicant;
            return [
                'id'           => $req->id,
                'student_name' => $studentUser->fullname ?? 'Tidak Diketahui',
                'reason'       => $req->reason,
                'start_date'   => $req->start_date,
                'end_date'     => $req->end_date,
                'status'       => $req->status_approval, // Menyimpan Pending, Approved, atau Rejected
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function approve(Request $request, $id)
    {
        return $this->updateStatus($request, $id, 'Approved', 'Izin disetujui.');
    }

    public function reject(Request $request, $id)
    {
        return $this->updateStatus($request, $id, 'Rejected', 'Izin ditolak.');
    }

   public function store(Request $request)
    {
        $user = $this->authorizeRoles($request, ['Siswa', 'Orang Tua']);
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'student_id' => 'sometimes|integer',
        ]);

        if ($user->role?->role_name === 'Siswa') {
            $studentId = $user->id;
        } else {
            $studentId = $validated['student_id'] ?? null;
            $linkedStudentIds = $user->linkedStudentIds();
            $studentId ??= $linkedStudentIds->count() === 1 ? $linkedStudentIds->first() : null;
            abort_unless($studentId && $linkedStudentIds->contains((int) $studentId), 403, 'Pilih siswa yang terhubung ke akun Anda.');
        }

        // Validasi Cegah Izin Dobel / Overlap Tanggal
        $existingLeave = LeaveRequest::where('student_id', $studentId)
            ->whereIn('status_approval', ['Pending', 'Approved'])
            ->where(function ($query) use ($validated) {
                // Logika mendeteksi jika tanggal bersinggungan
                $query->where('start_date', '<=', $validated['end_date'])
                      ->where('end_date', '>=', $validated['start_date']);
            })
            ->first();

        // Jika ditemukan izin yang bentrok, tolak!
        if ($existingLeave) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal: Siswa sudah memiliki pengajuan izin pada tanggal tersebut (' . $existingLeave->start_date . ' s/d ' . $existingLeave->end_date . ').'
            ], 409);
        }

        $photoPath = $request->file('photo')->store('leave_requests', 'public');
        $leave = LeaveRequest::create([
            'applicant_id'    => $user->id,
            'student_id'      => $studentId,
            'reason'          => $validated['reason'],
            'start_date'      => $validated['start_date'],
            'end_date'        => $validated['end_date'],
            'document_path'   => $photoPath,
            'status_approval' => 'Pending',
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Pengajuan izin berhasil dikirim.', 
            'data' => $leave
        ]);
    }

    public function mine(Request $request)
    {
        $user = $this->authorizeRoles($request, ['Siswa', 'Orang Tua']);
        $validated = $request->validate(['student_id' => 'sometimes|integer']);

        if ($user->role?->role_name === 'Siswa') {
            $studentIds = [$user->id];
        } else {
            if (isset($validated['student_id'])) {
                abort_unless($user->linkedStudentIds()->contains((int) $validated['student_id']), 403, 'Siswa tersebut tidak terhubung ke akun Anda.');
                $studentIds = [$validated['student_id']];
            } else {
                $studentIds = $user->linkedStudentIds()->all();
            }
        }

        $requests = LeaveRequest::with(['student:id,fullname'])
            ->whereIn('student_id', $studentIds)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (LeaveRequest $leave) => [
                'id' => $leave->id,
                'student_name' => $leave->student?->fullname,
                'reason' => $leave->reason,
                'start_date' => $leave->start_date,
                'end_date' => $leave->end_date,
                'status' => $leave->status_approval,
            ]);

        return response()->json(['success' => true, 'data' => $requests]);
    }

    private function updateStatus(Request $request, int|string $id, string $status, string $message)
    {
        $user = $this->authorizeRoles($request, ['Wali Kelas', 'Guru BK']);
        $query = LeaveRequest::query();

        if ($user->role?->role_name === 'Wali Kelas') {
            abort_unless($user->class_id, 403, 'Akun Wali Kelas belum terhubung ke kelas.');
            $query->whereHas('student', fn ($students) => $students->where('class_id', $user->class_id));
        }

        $leave = $query->findOrFail($id);
        $leave->update([
            'status_approval' => $status,
            'verified_by' => $user->id,
        ]);

        return response()->json(['success' => true, 'message' => $message]);
    }
}