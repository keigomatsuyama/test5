<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Http\Requests\UserAttendanceRequest;
use App\Models\AttendanceRequest;


class AdminController extends Controller
{
    /**
     * 管理者ログイン画面表示
     */
    public function adminlogin()
    {
        $admin = User::where('is_admin', true)->first();
        Auth::guard('admin')->logout();
        return view('admin_login', compact('admin'));
    }

    public function login(Request $request)
    {
        // ① 先にバリデーション
        $request->validate(
            [
                'email'    => ['required', 'email'],
                'password' => ['required'],
            ],
            [
                'email.required'    => 'メールアドレスを入力してください。',
                'email.email'       => 'メールアドレスの形式が正しくありません。',
                'password.required' => 'パスワードを入力してください。',
            ]
        );

     
    // 🔴 ここを変更
    if (!Auth::guard('admin')->attempt(
        $request->only('email', 'password')
    )) {
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    // 🔴 ここも変更
    if (!Auth::guard('admin')->user()->is_admin) {
        Auth::guard('admin')->logout();
        return back()->withErrors([
            'email' => '管理者アカウントではありません',
        ]);
    }

        // ④ セッション再生成
        $request->session()->regenerate();

        // ⑤ 管理者ログイン成功
        return redirect('/admin/attendance/list');
    }
public function index(Request $request)
{
    $date = Carbon::parse($request->input('date', today()));

    $users = User::where('is_admin', false)
        ->with([
            'attendances' => function ($query) use ($date) {
                $query->where('date', $date->toDateString())
                      ->with([
                          'breaks',
                          'attendanceRequests.breaks'
                      ]);
            }
        ])
        ->get();

    return view('admin_attendance_list', compact('users', 'date'));
}

public function detail(Attendance $attendance)
{
    $attendance->load([
        'user',
        'breaks',
        'attendanceRequests.breaks'
    ]);

    // 最新の修正申請
    $latestRequest = $attendance->attendanceRequests()
        ->with('breaks')
        ->latest()
        ->first();

    // 申請があればそれを表示、なければ確定データ
    $display = $latestRequest ?? $attendance;
$isEdit = true;
    return view('admin_attendance_detail', compact(
        'attendance',
        'display',
        'latestRequest',
        'isEdit'
    ));
}

    public function list()
    {
  $users = User::where('is_admin', false)->get();
        return view('admin_staff_list', compact('users'));
    }

    public function staff(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 表示月（なければ今月）
        $month = $request->query('month')
            ? Carbon::parse($request->query('month'))
            : Carbon::now();

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

       $attendances = $user->attendances()
    ->with('breaks','attendanceRequests.breaks')   // ← これを追加
    ->whereBetween('date', [$start, $end])
    ->orderBy('date')
    ->get();

        return view('admin_attendance_staff', compact(
            'user',
            'attendances',
            'month'
        ));
    }

public function export(Request $request, $id)
{
    // 月が無い場合の安全対策
    if (!$request->month) {
        abort(400, 'month parameter is required');
    }

    $month = Carbon::createFromFormat('Y-m', $request->month);

    $start = $month->copy()->startOfMonth();
    $end   = $month->copy()->endOfMonth();

    $attendances = Attendance::with('breaks')
        ->where('user_id', $id)
        ->whereBetween('date', [$start, $end])
        ->orderBy('date')
        ->get();

    $filename = 'attendance_' . $month->format('Y_m') . '.csv';

    $handle = fopen('php://temp', 'r+');

    // Excel文字化け防止（UTF-8 BOM）
    fwrite($handle, "\xEF\xBB\xBF");

    // ヘッダー
    fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

    foreach ($attendances as $attendance) {

        // 出勤・退勤を「時刻のみ」に変換
        $clockIn = $attendance->clock_in
            ? Carbon::parse($attendance->clock_in)->format('H:i')
            : '';

        $clockOut = $attendance->clock_out
            ? Carbon::parse($attendance->clock_out)->format('H:i')
            : '';

        // 休憩合計（分）
        $breakMinutes = $attendance->breaks->sum(function ($break) {
            if (!$break->break_start || !$break->break_end) return 0;

            return Carbon::parse($break->break_end)
                ->diffInMinutes(Carbon::parse($break->break_start));
        });

        // 勤務合計（分）
        $workMinutes = null;
        if ($attendance->clock_in && $attendance->clock_out) {
            $totalMinutes = Carbon::parse($attendance->clock_out)
                ->diffInMinutes(Carbon::parse($attendance->clock_in));

            $workMinutes = $totalMinutes - $breakMinutes;
        }

        // フォーマット変換
        $breakTime = $breakMinutes > 0
            ? sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60)
            : '';

        $workTime = $workMinutes !== null
            ? sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60)
            : '';

        // CSV出力
        fputcsv($handle, [
            Carbon::parse($attendance->date)->format('Y/m/d'),
            $clockIn,
            $clockOut,
            $breakTime,
            $workTime
        ]);
    }

    rewind($handle);

    return response(stream_get_contents($handle))
        ->header('Content-Type', 'text/csv; charset=UTF-8')
        ->header('Content-Disposition', "attachment; filename={$filename}");
}

    public function stamp(Request $request)
    {
        // 承認ステータス（tab切り替え用：任意）
        $status = $request->query('status', 'pending'); // pending / approved

        $requests = AttendanceRequest::with('user')
            ->when($status === 'pending', function ($query) {
                $query->where('status', '承認待ち');
            })
            ->when($status === 'approved', function ($query) {
                $query->where('status', '承認済み');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin_stamp', compact('requests', 'status'));
    }
    public function update(UserAttendanceRequest $request, Attendance $attendance)
{
    // 勤怠本体更新
    $attendance->update([
        'clock_in'  => $request->clock_in,
        'clock_out' => $request->clock_out,
        'remark'    => $request->remark,
    ]);

    // 🔥 既存の休憩を全削除
    $attendance->breaks()->delete();

    // 🔥 入力された休憩だけ再登録
    foreach ($request->breaks ?? [] as $break) {

        if (!empty($break['break_start']) && !empty($break['break_end'])) {

            $attendance->breaks()->create([
                'break_start' => $break['break_start'],
                'break_end'   => $break['break_end'],
            ]);
        }
    }

    // 🔥 ループの外に置く
    return redirect()
        ->route('admin.attendances.index')
        ->with('success', '勤怠を修正しました');
}
}