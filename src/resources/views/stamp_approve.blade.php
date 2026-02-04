<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>勤怠詳細</title>
  <link rel="stylesheet" href="{{ asset('css/stamp_approve.css') }}">
</head>
<body>
<header class="header">
  <div class="header-left">
    <img src="{{ asset('images/logo.png') }}" alt="COACHTECH">
  </div>

  <nav class="header-nav">
    <a href="{{ route('admin.attendances.index') }}">勤怠一覧</a>
    <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
    <a href="{{ route('admin.stamp.index') }}">申請一覧</a>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button class="logout-btn">ログアウト</button>
    </form>
  </nav>
</header>

<main class="container">

  <h1 class="title">｜ 勤怠詳細</h1>

  <div class="detail-card">
    <table class="detail-table">
      <tr>
        <th>名前</th>
        <td colspan="3">{{ $attendance->user->name }}</td>
      </tr>

      <tr>
        <th>日付</th>
        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</td>
        <td colspan="2">{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</td>
      </tr>

      <tr>
        <th>出勤・退勤</th>
        <td>{{ $attendance->clock_in }}</td>
        <td class="center">〜</td>
        <td>{{ $attendance->clock_out }}</td>
      </tr>

      {{-- 休憩1 --}}
      @php $break1 = $attendance->breaks->get(0); @endphp
      <tr>
        <th>休憩</th>
        <td>{{ $break1?->break_start }}</td>
        <td class="center">〜</td>
        <td>{{ $break1?->break_end }}</td>
      </tr>

      {{-- 休憩2 --}}
      @php $break2 = $attendance->breaks->get(1); @endphp
      <tr>
        <th>休憩2</th>
        <td>{{ $break2?->break_start }}</td>
        <td class="center"></td>
        <td>{{ $break2?->break_end }}</td>
      </tr>

      <tr>
        <th>備考</th>
        <td colspan="3">{{ $attendance->remark }}</td>
      </tr>
    </table>
  </div>
  {{-- 管理者のみ表示 --}}
  @if(auth()->user()->is_admin)
    <div class="button-area">

      @if($request->status === 'pending')
        <!-- 承認前 -->
        <form method="POST"
              action="{{ route('admin.stamp.approve', $request->id) }}">
          @csrf
          <button type="submit" class="approve-btn">承認</button>
        </form>

      @elseif($request->status === 'approved')
        <!-- 承認済み -->
        <button class="approved-btn" disabled>承認済み</button>
      @endif

    </div>
  @endif

</main>

</body>
</html>
