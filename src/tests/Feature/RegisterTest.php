<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 会員登録画面が表示できる()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('会員登録');
    }

    /** @test */
    public function 正しい情報で登録できる()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $this->assertAuthenticated();
    }

    /** @test */
    public function パスワードが一致しないと登録できない()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrongpass',
        ]);
        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }
}