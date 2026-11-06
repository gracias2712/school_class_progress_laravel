<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiMobileAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_read_the_staff_attendance_report(): void
    {
        $student = $this->userWithRole('Siswa');

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/attendances/report')
            ->assertForbidden();
    }

    public function test_student_cannot_submit_a_teaching_journal(): void
    {
        $student = $this->userWithRole('Siswa');

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/journals', [])
            ->assertForbidden();
    }

    public function test_guardian_can_only_load_their_linked_children(): void
    {
        $guardian = $this->userWithRole('Orang Tua');

        $this->actingAs($guardian, 'sanctum')
            ->getJson('/api/children')
            ->assertOk()
            ->assertExactJson(['success' => true, 'data' => []]);
    }

    public function test_guardian_child_list_uses_the_linked_student_id(): void
    {
        $student = $this->userWithRole('Siswa');
        $guardian = $this->userWithRole('Orang Tua', [
            'parent_id' => $student->id,
        ]);

        $this->actingAs($guardian, 'sanctum')
            ->getJson('/api/children')
            ->assertOk()
            ->assertJsonPath('data.0.id', $student->id)
            ->assertJsonPath('data.0.fullname', 'Siswa');
    }

    public function test_guardian_cannot_select_an_unlinked_student_schedule(): void
    {
        $linkedStudent = $this->userWithRole('Siswa');
        $otherStudent = $this->userWithRole('Siswa', [
            'username' => 'other-student',
            'fullname' => 'Siswa lain',
        ]);
        $guardian = $this->userWithRole('Orang Tua', [
            'parent_id' => $linkedStudent->id,
        ]);

        $this->actingAs($guardian, 'sanctum')
            ->getJson('/api/schedules?cycle_type=1&student_id='.$otherStudent->id)
            ->assertForbidden();
    }

    public function test_unsupported_web_role_cannot_use_mobile_schedule_api(): void
    {
        $waka = $this->userWithRole('Waka Kurikulum');

        $this->actingAs($waka, 'sanctum')
            ->getJson('/api/schedules?cycle_type=1')
            ->assertForbidden();
    }

    private function userWithRole(string $roleName, array $attributes = []): User
    {
        $role = Role::create(['role_name' => $roleName]);

        return User::create(array_merge([
            'role_id' => $role->id,
            'username' => strtolower(str_replace(' ', '-', $roleName)).'-'.Str::uuid(),
            'fullname' => $roleName,
            'password' => Hash::make(Str::random(32)),
        ], $attributes));
    }
}
