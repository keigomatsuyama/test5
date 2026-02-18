<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>勤怠一覧</title>
  <link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
</head>

<body>

  <header class="header">
    <img src="{{ asset('images/logo.png') }}" alt="ロゴ">
    <nav class="nav">
      <a href="{{ route('attendance.index') }}">勤怠</a>
      <a href="{{ route('attendance.list') }}">勤怠一覧</a>
      <a href="{{ route('stamp.index') }}">申請</a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="logout-link">ログアウト</button>
      </form>
    </nav>
  </header>

  <main class="container">
    <h1 class="title">勤怠一覧</h1>

    @php
    $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
    $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
    @endphp

    <div class="month-nav">
      <a class="month-btn"
        href="{{ route('attendance.list', ['month' => $prevMonth]) }}">
        ← 前月
      </a>

      <div class="month-label">
        📅 {{ $currentMonth->format('Y/m') }}
      </div>

      <a class="month-btn"
        href="{{ route('attendance.list', ['month' => $nextMonth]) }}">
        翌月 →
      </a>
    </div>

    <table class="attendance-table">
      <thead>
        <tr>
          <th>日付</th>
          <th>出勤</th>
          <th>退勤</th>
          <th>休憩</th>
          <th>合計</th>
          <th>詳細</th>
        </tr>
      </thead>

      <tbody>
        @foreach ($attendances as $attendance)
@php
$display = $attendance;
$breakMinutes = 0;
$totalMinutes = 0;

// 最新申請を取得
$latestRequest = $attendance->attendanceRequests
    ->sortByDesc('id')
    ->first();

// 申請があれば優先表示
if ($latestRequest) {
    $display = $latestRequest;
}

// 休憩計算
$breakMinutes = $display->breaks->sum(function ($b) {
    if ($b->break_start && $b->break_end) {
        return \Carbon\Carbon::parse($b->break_end)
            ->diffInMinutes(\Carbon\Carbon::parse($b->break_start));
    }
    return 0;
});

// 勤務時間計算（マイナス防止）
if ($display->clock_in && $display->clock_out) {

    $workMinutes =
        \Carbon\Carbon::parse($display->clock_out)
        ->diffInMinutes(\Carbon\Carbon::parse($display->clock_in));

    $totalMinutes = max(0, $workMinutes - $breakMinutes);
}

$fmt = fn($m) =>
    $m !== null
    ? floor($m/60).':'.sprintf('%02d',$m%60)
    : '';
@endphp

<tr>
<td>
{{ \Carbon\Carbon::parse($attendance->date)
    ->locale('ja')
    ->translatedFormat('m/d(D)') }}
</td>

<td>
{{ $display->clock_in
    ? \Carbon\Carbon::parse($display->clock_in)->format('H:i')
    : '' }}
</td>

<td>
{{ $display->clock_out
    ? \Carbon\Carbon::parse($display->clock_out)->format('H:i')
    : '' }}
</td>

<td>{{ $fmt($breakMinutes) }}</td>

<td>{{ $fmt($totalMinutes) }}</td>

<td>
<a href="{{ route('attendance.detail', $attendance->id) }}">
詳細
</a>
</td>

</tr>
@endforeach

      </tbody>
    </table>
  </main>

</body>

</html>