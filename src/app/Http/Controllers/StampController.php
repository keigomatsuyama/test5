<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
class StampController extends Controller
{public function index()
{
    $query = AttendanceRequest::with(['user', 'attendance']);

    if (auth()->user()->is_admin) {
        // ★ 管理者：一般ユーザーの申請のみ
        $query->whereHas('user', function ($q) {
            $q->where('is_admin', false);
        });
    } else {
        // ★ 一般ユーザー：自分の申請のみ
        $query->where('user_id', auth()->id());
    }

    $pendingRequests = (clone $query)
        ->where('status', 'pending')
        ->orderByDesc('created_at')
        ->get();

    $approvedRequests = (clone $query)
        ->where('status', 'approved')
        ->orderByDesc('created_at')
        ->get();

    return view('attendance_stamp', compact(
        'pendingRequests',
        'approvedRequests'
    ));
}


public function show($attendance_correct_request_id)
{
    // 勤怠修正申請を取得
    $request = AttendanceRequest::with([
        'attendance.user',
        'attendance.breaks'
    ])->findOrFail($attendance_correct_request_id);

    // 紐づく勤怠を取得
    $attendance = $request->attendance;

    return view('admin_attendance_detail', compact('attendance', 'request'));
}


}
