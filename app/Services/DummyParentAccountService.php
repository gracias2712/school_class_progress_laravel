<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class DummyParentAccountService
{
    public function ensureForAllStudents(): void
    {
        User::query()
            ->where('role_id', 5)
            ->orderBy('id')
            ->each(fn (User $student) => $this->ensureForStudent($student));
    }

    public function ensureForStudent(User $student): User
    {
        if ((int) $student->role_id !== 5) {
            throw new RuntimeException('Akun orang tua hanya dapat dibuat untuk pengguna ber-role Siswa.');
        }

        return DB::transaction(function () use ($student): User {
            $student = User::query()->lockForUpdate()->findOrFail($student->id);
            $parentRoleId = Role::query()->where('role_name', 'Orang Tua')->value('id');

            if ($parentRoleId === null) {
                throw new RuntimeException('Role Orang Tua belum tersedia. Jalankan RolesSeeder terlebih dahulu.');
            }

            $parents = User::query()
                ->where('role_id', $parentRoleId)
                ->where(function ($query) use ($student) {
                    $query->where('student_id', $student->id)
                        ->orWhere('parent_id', $student->id);
                })
                ->get();

            if ($parents->count() > 1) {
                throw new RuntimeException("Siswa {$student->id} terhubung ke lebih dari satu akun Orang Tua.");
            }

            $parent = $parents->first();

            if ($parent) {
                foreach (['student_id', 'parent_id'] as $column) {
                    if ($parent->{$column} !== null && (int) $parent->{$column} !== (int) $student->id) {
                        throw new RuntimeException("Relasi akun Orang Tua {$parent->id} menunjuk ke siswa yang berbeda.");
                    }
                }

                $parent->student_id = $student->id;
                $parent->parent_id = $student->id;
                $parent->save();

                return $parent;
            }

            $usernameBase = 'ortu_'.Str::limit(
                Str::lower($student->nisn_nip ?: 'siswa_'.$student->id),
                40,
                '',
            );
            $username = $usernameBase;
            $suffix = 1;

            while (User::query()->where('username', $username)->exists()) {
                $usernameSuffix = '_'.$student->id.($suffix > 1 ? '_'.$suffix : '');
                $username = Str::limit($usernameBase, 50 - strlen($usernameSuffix), '').$usernameSuffix;
                $suffix++;
            }

            return User::query()->create([
                'role_id' => $parentRoleId,
                'student_id' => $student->id,
                'parent_id' => $student->id,
                'username' => $username,
                'password' => Hash::make('password'),
                'fullname' => Str::limit('ORANG TUA - '.$student->fullname, 100, ''),
            ]);
        });
    }
}
