<?php

namespace Database\Seeders;
use App\Models\Attendance;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{

    public function run()
    {
        Attendance::create([
            'user_id' => 1,
            'date' => '2023-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 1,
        ]);
    }
}
