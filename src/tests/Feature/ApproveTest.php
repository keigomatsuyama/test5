<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApproveTest extends TestCase
{
    use RefreshDatabase;

    /* ============================
     * 👤 一般ユーザー：自分の申請のみ表示
     * ============================ */

    public function test_user_sees_only_own_requests()
    {
        $user = User::create([
            'name' => 'user1',
            'email' => 'user1@test.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $otherUser = User::create([
            'name' => 'user2',
            'email' => 'user2@test.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'web');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 1,
        ]);

        $otherAttendance = Attendance::create([
            'user_id' => $otherUser->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 1,
        ]);

        $ownRequest = AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_date' => now()->toDateString(),
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remark' => '自分',
            'status' => 'pending',
        ]);

        $otherRequest = AttendanceRequest::create([
            'user_id' => $otherUser->id,
            'attendance_id' => $otherAttendance->id,
            'request_date' => now()->toDateString(),
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remark' => '他人',
            'status' => 'pending',
        ]);

        $response = $this->get('/stamp_correction_request/list');

        $response->assertStatus(200);
       $response->assertSee('自分');
$response->assertDontSee('他人');
    }

    /* ============================
     * 👑 管理者：一般ユーザーの申請のみ表示
     * ============================ */

    public function test_admin_sees_only_non_admin_requests()
    {
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'user',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin,'web');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 1,
        ]);

        $request = AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_date' => now()->toDateString(),
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remark' => '修正',
            'status' => 'pending',
        ]);

        $response = $this->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee((string)$request->id);
    }

    /* ============================
     * 詳細表示
     * ============================ */

    public function test_can_view_request_detail()
    {
        $user = User::create([
            'name' => 'user',
            'email' => 'user3@test.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 1,
        ]);

        $request = AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_date' => now()->toDateString(),
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remark' => '修正',
            'status' => 'pending',
        ]);

        $response = $this->get(
            "/stamp_correction_request/{$request->id}"
        );

        $response->assertStatus(200);
        $response->assertViewHas('attendance');
        $response->assertViewHas('request');
    }

    public function test_show_returns_404_if_not_found()
    {
        $user = User::create([
            'name' => 'user',
            'email' => 'user4@test.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/stamp_correction_request/999');

        $response->assertStatus(404);
    }
    protected function setUp(): void
{
    parent::setUp();

    $this->withoutMiddleware(
        \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class
    );
}
}