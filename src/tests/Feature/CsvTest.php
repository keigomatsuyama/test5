<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CsvTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function 管理者はスタッフの勤怠CSVを出力できる()
  {
   $admin = User::factory()->create([
    'is_admin' => true
]);
    $staff = User::factory()->create();

    Attendance::create([
      'user_id' => $staff->id,
      'date' => '2024-01-10',
      'clock_in' => '09:00:00',
      'clock_out' => '18:00:00',
      'status' => 3
    ]);

    $response = $this->actingAs($admin, 'admin')
    ->get(route('admin.attendance.csv', [
        'id' => $staff->id,
        'month' => '2024-01'
    ]));

    $response->assertStatus(200);

    // CSVヘッダー
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    // ダウンロードファイル名
    $response->assertHeader(
      'Content-Disposition',
      'attachment; filename=attendance_2024_01.csv'
    );

    // CSV内容
    $content = $response->getContent();

    $this->assertStringContainsString('日付', $content);
    $this->assertStringContainsString('2024/01/10', $content);
    $this->assertStringContainsString('09:00', $content);
    $this->assertStringContainsString('18:00', $content);
  }

  /** @test */
  public function monthパラメータが無いとエラーになる()
  {
      $admin = User::factory()->create([
        'is_admin' => true
    ]);
    $staff = User::factory()->create();

    $response = $this->actingAs($admin, 'admin')
      ->get(route('admin.attendance.csv', [
        'id' => $staff->id
      ]));

    $response->assertStatus(400);
  }
}
