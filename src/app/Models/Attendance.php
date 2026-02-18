<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'remark',
    ];
    protected $casts = [
    'clock_in'  => 'datetime:H:i',
    'clock_out' => 'datetime:H:i',
];
    // ユーザーに属する
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 休憩（複数）
    public function breaks()
    {
        return $this->hasMany(BreakTime::class)->orderBy('break_start');
    }

    // 修正申請（複数）
    public function attendanceRequests()
{
    return $this->hasMany(AttendanceRequest::class);
}


}
