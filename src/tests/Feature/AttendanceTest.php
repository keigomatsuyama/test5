<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceInputTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clock_in()
    {
       $user = User::factory()->create([
    'email_verified_at' => now(),
    'is_admin' => false,
]);

$this->actingAs($user, 'web');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'status' => 0,
        ]);

        $this->post('/attendance/clock-in');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 1,
        ]);
    }
}