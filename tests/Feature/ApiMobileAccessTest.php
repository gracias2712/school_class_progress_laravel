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

    public function test_mobile_user_can_update_username_and_email_on_their_own_account(): void
    {
        $teacher = $this->userWithRole('Guru');
        $token = $teacher->createToken('profile-test')->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/profile', [
                'username' => 'updated-teacher',
                'email' => 'updated-teacher@example.test',
            ])
            ->assertOk()
            ->assertJsonPath('data.username', 'updated-teacher')
            ->assertJsonPath('data.email', 'updated-teacher@example.test');

        $this->assertDatabaseHas('users', [
            'id' => $teacher->id,
            'username' => 'updated-teacher',
            'email' => 'updated-teacher@example.test',
        ]);
    }

    public function test_profile_route_is_registered_once_and_requires_sanctum_authentication(): void
    {
        $profileRoutes = collect(app('router')->getRoutes())
            ->filter(fn ($route) => $route->uri() === 'api/profile'
                && in_array('PUT', $route->methods(), true));

        $this->assertCount(1, $profileRoutes);
        $route = $profileRoutes->first();
        $this->assertSame(
            \App\Http\Controllers\Api\AuthController::class.'@updateProfile',
            $route->getActionName()
        );
        $this->assertContains('auth:sanctum', $route->gatherMiddleware());

        $this->putJson('/api/profile', [
            'username' => 'unauthenticated-user',
        ])->assertUnauthorized();
    }

    public function test_all_mobile_roles_can_update_their_own_profile(): void
    {
        foreach (['Guru', 'Wali Kelas', 'Guru BK', 'Siswa', 'Orang Tua'] as $roleName) {
            $user = $this->userWithRole($roleName);
            $username = strtolower(str_replace(' ', '-', $roleName)).'-updated';

            $this->actingAs($user, 'sanctum')
                ->putJson('/api/profile', [
                    'username' => $username,
                    'email' => null,
                ])
                ->assertOk()
                ->assertJsonPath('data.id', $user->id)
                ->assertJsonPath('data.username', $username)
                ->assertJsonPath('data.email', null);

            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'username' => $username,
                'email' => null,
            ]);
        }
    }

    public function test_profile_update_allows_unchanged_unique_values_and_clearing_email(): void
    {
        $user = $this->userWithRole('Siswa', [
            'username' => 'existing-student',
            'email' => 'existing-student@example.test',
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/profile', [
                'username' => 'existing-student',
                'email' => 'existing-student@example.test',
            ])
            ->assertOk();

        $this->putJson('/api/profile', [
            'username' => 'renamed-student',
            'email' => null,
        ])
            ->assertOk()
            ->assertJsonPath('data.username', 'renamed-student')
            ->assertJsonPath('data.email', null);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'renamed-student',
            'email' => null,
        ]);
    }

    public function test_profile_update_rejects_another_users_username_or_email(): void
    {
        $this->userWithRole('Guru', [
            'username' => 'taken-username',
            'email' => 'taken@example.test',
        ]);
        $student = $this->userWithRole('Siswa');

        $this->actingAs($student, 'sanctum')
            ->putJson('/api/profile', [
                'username' => 'taken-username',
                'email' => 'taken@example.test',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['username', 'email']);
    }

    public function test_profile_update_validates_required_username_and_email_format(): void
    {
        $student = $this->userWithRole('Siswa');

        $this->actingAs($student, 'sanctum')
            ->putJson('/api/profile', [
                'email' => 'not-an-email',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['username', 'email']);

        $this->putJson('/api/profile', [
            'username' => str_repeat('u', 51),
            'email' => 'student@example.test',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['username']);
    }

    public function test_waka_cannot_update_a_profile_through_the_mobile_api(): void
    {
        $waka = $this->userWithRole('Waka Kurikulum');

        $this->actingAs($waka, 'sanctum')
            ->putJson('/api/profile', [
                'username' => 'waka-updated',
                'email' => 'waka@example.test',
            ])
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
