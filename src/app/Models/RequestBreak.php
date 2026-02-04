<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestBreak extends Model
{
    protected $table = 'attendance_request_breaks'; // ★ 必須

    protected $fillable = [
        'attendance_request_id',
        'break_start',
        'break_end',
        'order',
    ];

    public function attendanceRequest()
    {
        return $this->belongsTo(
            AttendanceRequest::class,
            'attendance_request_id'
        );
    }
}
