<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Http\Requests\LoginRequest;
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
        Auth::logout();
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

        // ② 認証（1回だけ）
        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors([
                'email' => 'ログイン情報が登録されていません',
            ]);
        }

        // ③ 管理者でなければ弾く
        if (!Auth::user()->is_admin) {
            Auth::logout();
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
                $query->where('date', $date->toDateString());
            },
            'attendances.breaks'
        ])
        ->get();

    return view('admin_attendance_list', compact('users', 'date'));
}

    public function detail(Attendance $attendance)
    {
        $attendance->load(['user', 'breaks']);

        return view('admin_attendance_detail', compact('attendance'));
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
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        return view('admin_attendance_staff', compact(
            'user',
            'attendances',
            'month'
        ));
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

    // 休憩更新
    foreach ($request->breaks ?? [] as $index => $break) {
        if (empty($break['break_start']) || empty($break['break_end'])) {
            continue;
        }

        $attendance->breaks()->updateOrCreate(
            ['id' => $attendance->breaks[$index]->id ?? null],
            [
                'break_start' => $break['break_start'],
                'break_end'   => $break['break_end'],
            ]
        );
    }

    return redirect()
        ->route('admin.attendances.index')
        ->with('success', '勤怠を修正しました');
}
}