<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiLeaveAndScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_submit_leave_overlapping_pending_or_approved_requests(): void
    {
        Storage::fake('public');

        foreach (['Pending', 'Approved'] as $index => $status) {
            $student = $this->userWithRole('Siswa');

            LeaveRequest::create([
                'student_id' => $student->id,
                'applicant_id' => $student->id,
                'start_date' => '2026-05-01',
                'end_date' => '2026-05-05',
                'reason' => 'Existing request',
                'document_path' => 'leave_requests/existing.jpg',
                'status_approval' => $status,
            ]);

            $this->actingAs($student, 'sanctum')
                ->postJson('/api/leave-requests', [
                    'reason' => 'New request',
                    'start_date' => '2026-05-05',
                    'end_date' => '2026-05-07',
                    'photo' => UploadedFile::fake()->image('proof.jpg'),
                ])
                ->assertConflict()
                ->assertJsonPath('success', false);

            $this->assertDatabaseCount('leave_requests', $index + 1);
        }
    }

    public function test_guru_and_wali_kelas_fetch_schedules_assigned_to_their_user(): void
    {
        $classe = Classe::create(['class_name' => 'Test class']);
        $room = Room::create(['room_name' => 'Test room']);

        foreach (['Guru', 'Wali Kelas'] as $roleName) {
            $user = $this->userWithRole($roleName);
            $otherTeacher = $this->userWithRole($roleName);

            $schedule = Schedule::create([
                'teacher_id' => $user->id,
                'class_id' => $classe->id,
                'room_id' => $room->id,
                'subject' => 'Matematika',
                'day_of_week' => 'Senin',
                'start_time' => '08:00',
                'end_time' => '09:00',
                'cycle_type' => 'Putaran 1',
            ]);

            Schedule::create([
                'teacher_id' => $otherTeacher->id,
                'class_id' => $classe->id,
                'room_id' => $room->id,
                'subject' => 'Bahasa Indonesia',
                'day_of_week' => 'Senin',
                'start_time' => '09:00',
                'end_time' => '10:00',
                'cycle_type' => 'Putaran 1',
            ]);

            $this->actingAs($user, 'sanctum')
                ->getJson('/api/schedules')
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.id', $schedule->id);
        }
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => $roleName]);

        return User::create([
            'role_id' => $role->id,
            'username' => strtolower(str_replace(' ', '-', $roleName)).'-'.Str::uuid(),
            'fullname' => $roleName,
            'password' => Hash::make(Str::random(32)),
        ]);
    }
}
