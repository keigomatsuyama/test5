<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>月次勤怠 | COACHTECH</title>
  <link rel="stylesheet" href="{{ asset('css/admin_attendance_staff.css') }}">
</head>
<body>

<header class="header">
    <img src="{{ asset('images/logo.png') }}" alt="ロゴ">
  <nav class="header-nav">
    <a href="{{ route('admin.attendances.index') }}">勤怠一覧</a>
    <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
    <a href="{{ route('admin.stamp.index') }}">申請一覧</a>
    <a href="">ログアウト</a>
  </nav>
</header>

<main class="container">

  {{-- タイトル --}}
  <h1 class="title">｜ {{ $user->name }}さんの勤怠</h1>

  {{-- 月切り替え --}}
  <div class="month-nav">
    <a
      href="{{ route('admin.attendance.staff', [
        $user->id,
        'month' => $month->copy()->subMonth()->format('Y-m')
      ]) }}"
      class="month-btn"
    >
      ← 前月
    </a>

    <div class="month-center">
      📅 {{ $month->format('Y/m') }}
    </div>

    <a
      href="{{ route('admin.attendance.staff', [
        $user->id,
        'month' => $month->copy()->addMonth()->format('Y-m')
      ]) }}"
      class="month-btn"
    >
      翌月 →
    </a>
  </div>

  {{-- テーブル --}}
  <div class="table-wrapper">
    <table>
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
            $breakMinutes = $attendance->breaks->sum(function ($break) {
              if (!$break->break_end) return 0;
              return \Carbon\Carbon::parse($break->break_end)
                ->diffInMinutes(\Carbon\Carbon::parse($break->break_start));
            });

            $workMinutes = null;
            if ($attendance->clock_in && $attendance->clock_out) {
              $workMinutes =
                \Carbon\Carbon::parse($attendance->clock_out)
                  ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_in))
                - $breakMinutes;
            }
          @endphp

          <tr>
            <td>
              {{ \Carbon\Carbon::parse($attendance->date)->translatedFormat('m/d(D)') }}
            </td>

            <td>{{ $attendance->clock_in ?? '-' }}</td>
            <td>{{ $attendance->clock_out ?? '-' }}</td>

            <td>
              {{ $breakMinutes ? sprintf('%d:%02d', intdiv($breakMinutes,60), $breakMinutes%60) : '-' }}
            </td>

            <td>
              {{ $workMinutes !== null
                  ? sprintf('%d:%02d', intdiv($workMinutes,60), $workMinutes%60)
                  : '-' }}
            </td>

            <td>
              <a
                href="{{ route('admin.attendances.detail', $attendance->id) }}"
                class="detail"
              >
                詳細
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- CSV --}}
  <div class="csv-area">
    <button class="csv-btn">CSV出力</button>
  </div>

</main>

</body>
</html>
