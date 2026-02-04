<?php

namespace Database\Seeders;

use App\Models\BreakTime;
use Illuminate\Database\Seeder;

class BreakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // attendance_id = 1 の勤怠に対する休憩
        BreakTime::create([
            'attendance_id' => 1,
            'break_start' => '12:00:00',
            'break_end'   => '13:00:00',
        ]);

        // 休憩が複数ある例（任意）
        BreakTime::create([
            'attendance_id' => 1,
            'break_start' => '15:00:00',
            'break_end'   => '15:15:00',
        ]);
    }
}
