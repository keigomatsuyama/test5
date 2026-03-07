<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\RequestBreak;
use App\Http\Requests\UserAttendanceRequest;
use Illuminate\Support\Facades\Auth;
class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'date' => today(),
            ],
            [
                'status' => 0, // 勤務外
            ]
        );

        return view('attendance', compact('attendance'));
    }

    public function clockIn()
    {
        $attendance = $this->todayAttendance();

        $attendance->update([
            'clock_in' => now()->format('H:i'),
            'status' => 1,
        ]);

        return back();
    }

    public function breakIn()
    {
        $attendance = $this->todayAttendance();

        $attendance->breaks()->create([
            'break_start' => now()->format('H:i'),
        ]);

        $attendance->update(['status' => 2]);

        return back();
    }

    public function breakOut()
    {
        $attendance = $this->todayAttendance();

        $attendance->breaks()
            ->whereNull('break_end')
            ->latest()
            ->first()
            ->update([
                'break_end' => now()->format('H:i'),
            ]);

        $attendance->update(['status' => 1]);

        return back();
    }

    public function clockOut()
    {
        $attendance = $this->todayAttendance();

        $attendance->update([
            'clock_out' => now()->format('H:i'),
            'status' => 3,
        ]);

        return back();
    }

    private function todayAttendance()
    {
        return Attendance::where('user_id', Auth::id())
            ->where('date', today())
            ->firstOrFail();
    }
    public function list(Request $request)
{
    $month = $request->query('month')
        ? \Carbon\Carbon::createFromFormat('Y-m', $request->query('month'))
        : now();

    $query = Attendance::with(['breaks', 'user'])
        ->whereMonth('date', $month->month)
        ->whereYear('date', $month->year);
    if (Auth::user()->is_admin) {
        // ★ 管理者：一般ユーザーのみ表示
        $query->whereHas('user', function ($q) {
            $q->where('is_admin', false);
        });
    } else {
        // ★ 一般ユーザー：自分だけ
        $query->where('user_id', Auth::id());
    }

    $attendances = $query
        ->orderBy('date')
        ->get();

    return view('attendance_list', [
        'attendances'  => $attendances,
        'currentMonth'=> $month,
    ]);
}
public function detail(Request $request, $id)
{

    $attendance = Attendance::with(['user', 'breaks'])
        ->findOrFail($id);

    $latestRequest = AttendanceRequest::with('breaks')
        ->where('attendance_id', $attendance->id)
        ->orderBy('created_at', 'desc')
        ->first();

    $isPending  = $latestRequest?->status === 'pending';
    $isApproved = $latestRequest?->status === 'approved';

    // ★ 表示用データ
  if (session()->has('errors')) {
    // バリデーションエラー時は入力値（old）を優先
    $display = $attendance;
} else {
    $display = $isPending ? $latestRequest : $attendance;
}
    $isEdit = !$isPending && !$isApproved;

    return view('attendance_detail', compact(
        'attendance',
        'display',
        'isPending',
        'isApproved',
        'isEdit'
    ));
}
public function update(UserAttendanceRequest $request, $id)
{
    $attendance = Attendance::findOrFail($id);
    $data = $request->validated();

    $requestModel = AttendanceRequest::create([
        'user_id'       => auth()->id(),
        'attendance_id' => $attendance->id,
        'request_date'  => $attendance->date,
        'clock_in'      => $data['clock_in'],
        'clock_out'     => $data['clock_out'],
        'remark'        => $data['remark'],
        'status'        => 'pending',
    ]);

    foreach ($data['breaks'] ?? [] as $break) {

        if (empty($break['break_start']) || empty($break['break_end'])) {
            continue;
        }

        RequestBreak::create([
            'attendance_request_id' => $requestModel->id,
            'break_start' => $break['break_start'],
            'break_end'   => $break['break_end'],
        ]);
    }

    return redirect()->route('attendance.detail', $attendance->id);
}
public function stamp()
{
    return view('attendance_stamp'); // 表示したいBlade
}
}
