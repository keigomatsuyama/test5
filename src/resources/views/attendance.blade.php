<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>勤怠管理</title>
  <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
</head>
<body>

<header class="header">
    <img src="{{ asset('images/logo.png') }}" alt="ロゴ">

  <nav class="nav">
    @if ($attendance->status === 3)
      {{-- 退勤後 --}}
      <a href="{{ route('attendance.list') }}">今月の出勤一覧</a>
      <a >申請</a>
    @else
      {{-- 通常 --}}
      <a href="{{ route('attendance.index') }}">勤怠</a>
      <a href="{{ route('attendance.list') }}">勤怠一覧</a>
    <a href="{{ route('stamp.index') }}">申請</a>
    @endif

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="logout-link">ログアウト</button>
    </form>
  </nav>
</header>

<main class="container">

  {{-- ステータス表示 --}}
  @if ($attendance->status === 0)
    <span class="status">勤務外</span>
  @elseif ($attendance->status === 1)
    <span class="status">出勤中</span>
  @elseif ($attendance->status === 2)
    <span class="status">休憩中</span>
  @elseif ($attendance->status === 3)
    <span class="status">退勤済</span>
  @endif

  {{-- 日付・時刻 --}}
  <p class="date">{{ now()->locale('ja')->translatedFormat('Y年n月j日(D)') }}
</p>
  <p class="time">{{ now()->format('H:i') }}</p>

  {{-- ボタン・メッセージ切り替え --}}
  <div class="buttons">

    {{-- 勤務外 --}}
    @if ($attendance->status === 0)
      <form method="POST" action="{{ route('attendance.clockIn') }}">
        @csrf
        <button class="btn btn-black">出勤</button>
      </form>

    {{-- 出勤中 --}}
    @elseif ($attendance->status === 1)
      <form method="POST" action="{{ route('attendance.clockOut') }}">
        @csrf
        <button class="btn btn-black">退勤</button>
      </form>

      <form method="POST" action="{{ route('attendance.breakIn') }}">
        @csrf
        <button class="btn btn-white">休憩入</button>
      </form>

    {{-- 休憩中 --}}
    @elseif ($attendance->status === 2)
      <form method="POST" action="{{ route('attendance.breakOut') }}">
        @csrf
        <button class="btn btn-white">休憩戻</button>
      </form>

    {{-- 退勤済 --}}
    @elseif ($attendance->status === 3)
      <p class="finish">お疲れ様でした。</p>
    @endif

  </div>
</main>

</body>
</html>
