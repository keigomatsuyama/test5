<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>申請一覧 | COACHTECH</title>
  <link rel="stylesheet" href="{{ asset('css/stamp_approve.css') }}">
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
    <form id="logout-form"  action="{{ route('logout') }}" method="POST" style="display:none;">
      @csrf
    </form>
  </nav>
</header>

<main class="container">
  <h1 class="title">勤怠詳細</h1>

  <div class="card">

    {{-- 名前 --}}
    <div class="row">
      <div class="label">名前</div>
      <div class="value">{{ $attendance->user->name }}</div>
    </div>

    {{-- 日付 --}}
    <div class="row">
      <div class="label">日付</div>
      <div class="value">
           {{ \Carbon\Carbon::parse($attendance->date)->locale('ja')->translatedFormat('Y年n月j日(D)') }}
      </div>
    </div>

    {{-- 出勤・退勤 --}}
   {{-- 出勤・退勤 --}}
<div class="row">
  <div class="label">出勤・退勤</div>
<div class="value">
    <div class="split">
    <input type="text" name="clock_in" value="{{ optional($request->clock_in)->format('H:i') }}" readonly>
    〜
    <input type="text" name="clock_out" value="{{ optional($request->clock_out)->format('H:i') }}" readonly>
  </div>
</div>
    </div>

   @php
  $break1 = $request->breaks->get(0);
  $break2 = $request->breaks->get(1);
@endphp


    {{-- 休憩 --}}
    <div class="row">
      <div class="label">休憩</div>
      <div class="value split">
        <input type="text" name="breaks[0][break_start]" value="{{ optional($break1?->break_start)->format('H:i') }}" readonly>
        〜
        <input type="text" name="breaks[0][break_end]" value="{{ optional($break1?->break_end)->format('H:i') }}" readonly>
      </div>
    </div>

    {{-- 休憩2 --}}
    <div class="row">
      <div class="label">休憩2</div>
      <div class="value split">
        <input type="text" name="breaks[1][break_start]" value="{{ optional($break2?->break_start)->format('H:i') }}" readonly>
        〜
        <input type="text" name="breaks[1][break_end]" value="{{ optional($break2?->break_end)->format('H:i') }}" readonly>
      </div>
    </div>
<div class="card">
    {{-- 備考 --}}
    <div class="row">
      <div class="label">備考</div>
      <div class="value">
        <textarea name="remark" readonly>{{ $request->remark }}</textarea>

      </div>
    </div>

  </div>

  {{-- 承認ボタン --}}
  @if(auth()->user()->is_admin)
    <div class="button-area">
      @if($request->status === 'pending')
        <form method="POST" action="{{ route('admin.stamp.approve', $request->id) }}">
          @csrf
          <button class="btn-black">承認</button>
        </form>
      @else
        <span class="approved-btn">承認済み</span>
      @endif
    </div>
  @endif

</main>

</body>
</html>
