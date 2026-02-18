<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateNewUser implements CreatesNewUsers
{
    public function create(array $input)
    {
        Validator::make($input, [
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ])->validate();

        return User::create([
            'username' => $input['username'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
