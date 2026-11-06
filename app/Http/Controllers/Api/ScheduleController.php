<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Journal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    use AuthorizesApiRoles;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cycle_type' => ['sometimes', 'in:1,2'],
            'student_id' => ['sometimes', 'integer'],
        ]);

        $user = $this->authorizeRoles($request, ['Guru', 'Wali Kelas', 'Siswa', 'Orang Tua']);
        $today = Carbon::today()->toDateString(); // Ambil tanggal hari ini di server

        $query = Schedule::query()
            ->with([
                'teacher:id,fullname',
                'classe:id,class_name',
                'room:id,room_name',
            ]);

        $roleName = $user->role?->role_name;
        if (in_array($roleName, ['Guru', 'Wali Kelas'], true)) {
            $query->where('teacher_id', $user->id);
        } elseif ($roleName === 'Siswa') {
            abort_unless($user->class_id, 403, 'Akun siswa belum terhubung ke kelas.');
            $query->where('class_id', $user->class_id);
        } else {
            $student = User::query()
                ->whereKey($user->parent_id)
                ->whereHas('role', fn ($roles) => $roles->where('role_name', 'Siswa'))
                ->first();
            abort_unless($student && $student->class_id, 403, 'Akun Orang Tua belum terhubung ke siswa.');
            abort_unless(
                !isset($validated['student_id']) || (int) $validated['student_id'] === (int) $student->id,
                403,
                'Siswa tersebut tidak terhubung ke akun Anda.'
            );
            $query->where('class_id', $student->class_id);
        }

        // Filter Putaran
        if (isset($validated['cycle_type'])) {
            $query->where('cycle_type', 'Putaran '.$validated['cycle_type']);
        }

        $schedules = $query
            ->orderByRaw("FIELD(day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('start_time')
            ->get([
                'id', 'teacher_id', 'class_id', 'room_id', 'subject', 'day_of_week', 'start_time', 'end_time', 'cycle_type',
            ]);

        return response()->json([
            'success' => true, 
            'data' => $schedules->map(fn (Schedule $schedule) => [
                'id' => $schedule->id,
                'subject' => $schedule->subject,
                'day_of_week' => $schedule->day_of_week,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'cycle_type' => $schedule->cycle_type,
                // INDIKATOR BARU: Mengecek apakah jurnal sudah diisi hari ini
                'is_filled_today' => Journal::where('schedule_id', $schedule->id)
                                            ->where('date', $today)
                                            ->exists(),
                'teacher' => [
                    'id' => $schedule->teacher?->id,
                    'fullname' => $schedule->teacher?->fullname,
                ],
                'class' => [
                    'id' => $schedule->classe?->id,
                    'class_name' => $schedule->classe?->class_name,
                ],
                'room' => [
                    'id' => $schedule->room?->id,
                    'room_name' => $schedule->room?->room_name,
                ],
            ]),
        ]);
    }
}