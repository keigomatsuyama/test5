<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\DB;;
class AdminStampController extends Controller
{
    /**
     * 管理者用：修正申請一覧
     */
  public function index()
{
    $pendingRequests = AttendanceRequest::with([
        'user',
        'attendance.user',
    ])
        ->where('status', 'pending')
        ->latest()
        ->get();

    $approvedRequests = AttendanceRequest::with([
        'user',
        'attendance.user',
    ])
        ->where('status', 'approved')
        ->latest()
        ->get();

    return view('admin_stamp', compact(
        'pendingRequests',
        'approvedRequests'
    ));
}



    /**
     * 修正申請 詳細（承認画面）
     */
    public function show($attendance_correct_request_id)
    {
        $request = AttendanceRequest::with([
            'user',
            'attendance.user',
            'attendance.breaks',
        ])->findOrFail($attendance_correct_request_id);

        $attendance = $request->attendance;

        return view('stamp_approve', compact('request', 'attendance'));
    }

    /**
     * 修正申請 承認処理
     */
    
public function approve($attendance_correct_request_id)
{
    $request = AttendanceRequest::with('breaks')
        ->findOrFail($attendance_correct_request_id);

    // すでに承認済みなら何もしない
    if ($request->status === 'approved') {
        return redirect()
            ->route('admin.stamp.index')
            ->with('error', 'この申請はすでに承認されています');
    }

    $attendance = $request->attendance;

    DB::transaction(function () use ($request, $attendance) {

        // ① 勤怠本体を更新
        $attendance->update([
            'clock_in'  => $request->clock_in,
            'clock_out' => $request->clock_out,
            'remark'    => $request->remark,
        ]);

        // ② 既存の休憩を全削除（この勤怠の分だけ）
        $attendance->breaks()->delete();

        // ③ 申請された休憩を反映
        foreach ($request->breaks as $break) {
            $attendance->breaks()->create([
                'break_start' => $break->break_start,
                'break_end'   => $break->break_end,
            ]);
        }

        // ④ ステータスを承認済みに
        $request->update([
            'status' => 'approved',
        ]);
    });

    return redirect()
        ->route('admin.stamp.index')
        ->with('success', '修正申請を承認しました');
}
}