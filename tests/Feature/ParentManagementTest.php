<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ParentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_management_page_opens_without_pivot_table_or_creating_dummy_accounts(): void
    {
        $this->createRoles();
        $waka = $this->createUser(1, 'waka', 'Waka');
        $this->createUser(5, '1001', 'Siswa Satu', ['nisn_nip' => '1001']);
        if (Schema::hasTable('guardian_student')) {
            Schema::drop('guardian_student');
        }

        $this->actingAs($waka)
            ->get(route('users.orangtua'))
            ->assertOk()
            ->assertSee('Akun dummy tidak dibuat otomatis.');

        $this->assertSame(0, User::query()->where('role_id', 6)->count());
    }

    public function test_waka_can_manually_create_a_parent_account_linked_to_one_student(): void
    {
        $this->createRoles();
        $waka = $this->createUser(1, 'waka', 'Waka');
        $student = $this->createUser(5, '1001', 'Siswa Satu', ['nisn_nip' => '1001']);

        $this->actingAs($waka)
            ->post(route('users.store'), [
                'role_id' => 6,
                'user_identifier' => 'Budi Santoso',
                'nisn_nip' => 'PARENT-01',
                'student_id' => $student->id,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'role_id' => 6,
            'username' => 'ortu_parent-01',
            'student_id' => $student->id,
            'parent_id' => $student->id,
            'fullname' => 'BUDI SANTOSO',
        ]);
    }

    private function createRoles(): void
    {
        foreach (['Waka Kurikulum', 'Guru', 'Wali Kelas', 'Guru BK', 'Siswa', 'Orang Tua'] as $roleName) {
            Role::query()->firstOrCreate(['role_name' => $roleName]);
        }
    }

    private function createUser(int $roleId, string $username, string $fullname, array $attributes = []): User
    {
        return User::query()->create(array_merge([
            'role_id' => $roleId,
            'username' => $username,
            'fullname' => $fullname,
            'password' => Hash::make('password'),
        ], $attributes));
    }
}
