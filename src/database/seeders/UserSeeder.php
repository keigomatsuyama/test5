<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
class UserSeeder extends Seeder
{
    public function run()
    {
        // 管理者
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => '管理者',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );

        // 一般ユーザー
        User::updateOrCreate(
            ['email' => 'user@user.com'],
            [
                'name' => '一般ユーザー',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );
    }
}
