<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DummyParentAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_management_creates_one_linked_dummy_account_for_every_student(): void
    {
        $this->createRoles();
        $waka = $this->createUser(1, 'waka', 'Waka');
        $studentOne = $this->createUser(5, '1001', 'Siswa Satu', ['nisn_nip' => '1001']);
        $studentTwo = $this->createUser(5, '1002', 'Siswa Dua', ['nisn_nip' => '1002']);

        $this->actingAs($waka)->get(route('users.orangtua'))->assertOk();

        $this->assertDatabaseHas('users', [
            'role_id' => 6,
            'username' => 'ortu_1001',
            'student_id' => $studentOne->id,
            'parent_id' => $studentOne->id,
        ]);
        $this->assertDatabaseHas('users', [
            'role_id' => 6,
            'username' => 'ortu_1002',
            'student_id' => $studentTwo->id,
            'parent_id' => $studentTwo->id,
        ]);

        $this->actingAs($waka)->get(route('users.orangtua'))->assertOk();

        $this->assertSame(2, User::query()->where('role_id', 6)->count());
        $guardian = User::query()->where('username', 'ortu_1001')->firstOrFail();
        $this->assertSame($studentOne->id, $guardian->student->id);
        $this->assertTrue(Hash::check('password', $guardian->password));
    }

    public function test_new_students_are_given_a_linked_dummy_parent_account(): void
    {
        $this->createRoles();
        $waka = $this->createUser(1, 'waka', 'Waka');

        $this->actingAs($waka)
            ->post(route('users.store'), [
                'role_id' => 5,
                'fullname' => 'Siswa Baru',
                'nisn_nip' => '2001',
            ])
            ->assertRedirect();

        $student = User::query()->where('username', '2001')->firstOrFail();
        $this->assertDatabaseHas('users', [
            'role_id' => 6,
            'username' => 'ortu_2001',
            'student_id' => $student->id,
            'parent_id' => $student->id,
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
