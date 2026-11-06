<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function pending(Request $request)
    {
        $user = $request->user();
        
        $leaveRequests = LeaveRequest::with(['student', 'applicant'])
            ->where('status_approval', 'Pending')
            ->where(function($query) use ($user) {
                if ($user->class_id) {
                    $query->whereHas('student', function($q) use ($user) {
                        $q->where('class_id', $user->class_id);
                    })->orWhereHas('applicant', function($q) use ($user) {
                        $q->where('class_id', $user->class_id);
                    });
                }
            })
            ->get();

        $data = $leaveRequests->map(function($req) {
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
        $leave = LeaveRequest::findOrFail($id);
        $leave->update([
            'status_approval' => 'Approved',
            'verified_by'     => $request->user()->id
        ]);
        return response()->json(['success' => true, 'message' => 'Izin disetujui.']);
    }

    public function reject(Request $request, $id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $leave->update([
            'status_approval' => 'Rejected',
            'verified_by'     => $request->user()->id
        ]);
        return response()->json(['success' => true, 'message' => 'Izin ditolak.']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('leave_requests', 'public');
        }

        // Ubah 'photo_path' menjadi 'document_path' agar sesuai dengan database
        $leave = LeaveRequest::create([
            'applicant_id'    => $request->user()->id,
            'student_id'      => $request->user()->id,
            'reason'          => $request->reason,
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date,
            'document_path'   => $photoPath,
            'status_approval' => 'Pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Pengajuan izin berhasil dikirim.', 'data' => $leave]);
    }
}