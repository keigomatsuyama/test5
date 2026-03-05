<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequestTest extends TestCase
{
    use RefreshDatabase;

    /* ============================
     * 👤 ユーザーが申請できる
     * ============================ */
    public function test_user_can_create_attendance_request()
    {
        $user = User::create([
            'name' => 'user',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(), // ← これ追加
        ]);
        $this->actingAs($user);
    $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 1,
        ]);

     $response = $this->actingAs($user, 'web')
    ->put(route('attendance.update', $attendance->id), [
        'clock_in' => '09:00',
        'clock_out' => '18:00',
        'remark' => '修正申請',
        'breaks' => [],
    ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    /* ============================
     * 👑 管理者が承認できる
     * ============================ */
    public function test_admin_can_approve_attendance_request()
    {
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin, 'admin');

        $attendance = Attendance::create([
            'user_id' => $admin->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 1,
        ]);

        $request = AttendanceRequest::create([
            'user_id' => $admin->id,
            'attendance_id' => $attendance->id,
            'request_date' => now()->toDateString(),
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remark' => '修正後',
            'status' => 'pending',
        ]);

        $response = $this->post(
            "/admin/stamp_correction_request/approve/{$request->id}"
        );

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        $updated = $attendance->fresh();

        $this->assertEquals(
            '10:00',
            \Carbon\Carbon::parse($updated->clock_in)->format('H:i')
        );

        $this->assertEquals(
            '19:00',
            \Carbon\Carbon::parse($updated->clock_out)->format('H:i')
        );
    }
}
