<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;

trait AuthorizesApiRoles
{
    protected function authorizeRoles(Request $request, array $roles): User
    {
        $user = $request->user();
        abort_unless(
            $user && in_array($user->role?->role_name, $roles, true),
            403,
            'Akun Anda tidak memiliki akses ke fitur ini.'
        );

        return $user;
    }
}
