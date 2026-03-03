<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /* ============================
     * ログイン画面表示
     * ============================ */

    public function test_admin_login_page_is_displayed()
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    /* ============================
     * 管理者ログイン成功
     * ============================ */

    public function test_admin_can_login()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/attendance/list');
    }

    /* ============================
     * 一般ユーザーはログイン不可
     * ============================ */

    public function test_non_admin_cannot_login_as_admin()
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors();
    }

    /* ============================
     * バリデーション
     * ============================ */

    public function test_admin_login_requires_email_and_password()
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }
}