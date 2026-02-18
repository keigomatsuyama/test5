<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>勤怠一覧</title>
  <link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
</head>

<body>

<header class="header">
  <img src="{{ asset('images/logo.png') }}" alt="ロゴ">

  <nav class="header-nav">
    <a href="{{ route('admin.attendances.index') }}">勤怠一覧</a>
    <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
    <a href="{{ route('admin.stamp.index') }}">申請一覧</a>
    <a href="{{ route('logout') }}"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
      ログアウト
    </a>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
      @csrf
    </form>
  </nav>
</header>

  <main class="container">
    <h1 class="title">
      {{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}の勤怠
    </h1>

    {{-- 日付切り替え --}}
    <div class="date-nav">
      <a class="date-btn"
        href="{{ route('admin.attendances.index', ['date' => \Carbon\Carbon::parse($date)->subDay()->toDateString()]) }}">
        ← 前日
      </a>

      <div class="date-center">
        📅 {{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}
      </div>

      <a class="date-btn"
        href="{{ route('admin.attendances.index', ['date' => \Carbon\Carbon::parse($date)->addDay()->toDateString()]) }}">
        翌日 →
      </a>
    </div>

    <table class="attendance-table">
      <thead>
        <tr>
          <th>名前</th>
          <th>出勤</th>
          <th>退勤</th>
          <th>休憩</th>
          <th>合計</th>
          <th>詳細</th>
        </tr>
      </thead>
      <tbody>@foreach ($users as $user)
@php
$attendance = $user->attendances->first();
$display = null;
$breakMinutes = 0;
$totalMinutes = 0;

if ($attendance) {

    $latestRequest = $attendance->attendanceRequests
        ->sortByDesc('id')
        ->first();

    $display = $latestRequest ?? $attendance;

    // 休憩合計
    $breakMinutes = $display->breaks->sum(function ($break) {
        if (!$break->break_end) return 0;

        return \Carbon\Carbon::parse($break->break_end)
            ->diffInMinutes(\Carbon\Carbon::parse($break->break_start));
    });

    // 勤務合計
    if ($display->clock_in && $display->clock_out) {

        $workMinutes =
            \Carbon\Carbon::parse($display->clock_out)
            ->diffInMinutes(\Carbon\Carbon::parse($display->clock_in));

        // マイナス防止
        $totalMinutes = max(0, $workMinutes - $breakMinutes);
    }
}
@endphp

<tr>
<td>{{ $user->name }}</td>

<td>
{{ $display && $display->clock_in
    ? \Carbon\Carbon::parse($display->clock_in)->format('H:i')
    : '-' }}
</td>

<td>
{{ $display && $display->clock_out
    ? \Carbon\Carbon::parse($display->clock_out)->format('H:i')
    : '-' }}
</td>

<td>
{{ $display
    ? sprintf('%d:%02d', intdiv($breakMinutes,60), $breakMinutes%60)
    : '-' }}
</td>

<td>
{{ $display
    ? sprintf('%d:%02d', intdiv($totalMinutes,60), $totalMinutes%60)
    : '-' }}
</td>

<td>
@if ($attendance)
<a href="{{ route('admin.attendances.detail', $attendance->id) }}" class="detail-link">
詳細
</a>
@else
-
@endif
</td>

</tr>
@endforeach

      </tbody>

    </table>
  </main>

</body>

</html>