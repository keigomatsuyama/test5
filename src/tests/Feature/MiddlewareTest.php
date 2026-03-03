<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    // 🔒 未ログインは attendance に入れない
    public function test_guest_cannot_access_attendance()
    {
        $response = $this->get('/attendance');

        $response->assertRedirect('/login');
    }

    // 🔒 未認証ユーザーは attendance に入れない
    public function test_unverified_user_cannot_access_attendance()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertRedirect('/email/verify');
    }

    // ✅ 認証済みユーザーは attendance に入れる
    public function test_verified_user_can_access_attendance()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
    }

    // 🔒 一般ユーザーは admin に入れない
    public function test_user_cannot_access_admin_area()
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get('/admin/attendance/list');

      $response->assertRedirect('/login');
    }

    // ✅ 管理者は admin に入れる
    public function test_admin_can_access_admin_area()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);
    }
}