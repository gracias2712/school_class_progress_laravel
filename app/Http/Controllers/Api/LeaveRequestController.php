<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    use AuthorizesApiRoles;

    public function pending(Request $request)
    {
        $user = $this->authorizeRoles($request, ['Wali Kelas', 'Guru BK']);

        $query = LeaveRequest::with(['student', 'applicant'])
            ->where('status_approval', 'Pending')
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
                'status'       => $req->status_approval,
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
            abort_unless(
                $studentId
                    && (int) $studentId === (int) $user->parent_id
                    && User::whereKey($studentId)
                        ->whereHas('role', fn ($roles) => $roles->where('role_name', 'Siswa'))
                        ->exists(),
                403,
                'Pilih siswa yang terhubung ke akun Anda.'
            );
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

        return response()->json(['success' => true, 'message' => 'Pengajuan izin berhasil dikirim.', 'data' => $leave]);
    }

    public function mine(Request $request)
    {
        $user = $this->authorizeRoles($request, ['Siswa', 'Orang Tua']);
        $validated = $request->validate(['student_id' => 'sometimes|integer']);

        if ($user->role?->role_name === 'Siswa') {
            $studentIds = [$user->id];
        } else {
            if (isset($validated['student_id'])) {
                abort_unless(
                    (int) $validated['student_id'] === (int) $user->parent_id
                        && User::whereKey($validated['student_id'])
                            ->whereHas('role', fn ($roles) => $roles->where('role_name', 'Siswa'))
                            ->exists(),
                    403,
                    'Siswa tersebut tidak terhubung ke akun Anda.'
                );
                $studentIds = [$validated['student_id']];
            } else {
                $studentIds = $user->parent_id ? [$user->parent_id] : [];
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