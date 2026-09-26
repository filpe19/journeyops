<?php

namespace App\Actions\Accounts;

use App\Enums\UserRole;
use App\Models\User;

class RegisterAccount
{
    /**
     * @param  array{name: string, email: string, password: string, account_type?: string|null}  $input
     */
    public function handle(array $input): User
    {
        $role = UserRole::from($input['account_type'] ?? config('accounts.default_type'));

        return User::create([
            'name' => $input['name'],
            'email' => strtolower($input['email']),
            'password' => $input['password'],
            'role' => $role,
        ]);
    }
}
