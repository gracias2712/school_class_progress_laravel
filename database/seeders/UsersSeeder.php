<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $username = env('INITIAL_ADMIN_USERNAME', 'wakakur');

        if (User::where('username', $username)->exists()) {
            return;
        }

        $password = env('INITIAL_ADMIN_PASSWORD');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('Set INITIAL_ADMIN_PASSWORD in the environment before running the database seeder.');
        }

        $role = \App\Models\Role::where('role_name', 'Waka Kurikulum')->firstOrFail();

        User::firstOrCreate(
            ['username' => $username],
            [
                'role_id' => $role->id,
                'password' => Hash::make($password),
                'fullname' => env('INITIAL_ADMIN_NAME', 'Waka Kurikulum'),
            ],
        );
    }
}