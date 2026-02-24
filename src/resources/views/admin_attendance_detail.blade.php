<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>勤怠詳細（管理者）</title>
  <link rel="stylesheet" href="{{ asset('css/admin_detail.css') }}">
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
  </nav>
</header>
<main class="container">
  <h1 class="title">勤怠詳細</h1>

  <form method="POST" action="{{ route('admin.attendances.update', $attendance->id) }}">
    @csrf
    @method('PATCH')

@php
  $latestRequest = $attendance->attendanceRequests->sortByDesc('id')->first();
  $display = $latestRequest ?? $attendance;
  $status = $latestRequest->status ?? null;
  $breaks = $display->breaks;
@endphp

  <div class="card">

    <!-- 名前 -->
    <div class="row">
      <div class="label">名前</div>
      <div class="value">{{ $attendance->user->name }}</div>
    </div>

    <!-- 日付 -->
    <div class="row">
      <div class="label">日付</div>
      <div class="value">
        {{ \Carbon\Carbon::parse($attendance->date)->locale('ja')->translatedFormat('Y年n月j日(D)') }}
      </div>
    </div>

    <!-- 出勤退勤 -->
 <div class="card">

  <!-- 出勤退勤 -->
  <div class="row">
    <div class="label">出勤・退勤</div>
    <div class="value">
      <div class="split">
        <input type="text" name="clock_in"
          value="{{ old('clock_in', optional($display->clock_in)->format('H:i')) }}">
        〜
        <input type="text" name="clock_out"
          value="{{ old('clock_out', optional($display->clock_out)->format('H:i')) }}">
      </div>
    </div>
  </div>

  @error('clock_in')
    <p class="error-message">{{ $message }}</p>
  @enderror
  @error('clock_out')
    <p class="error-message">{{ $message }}</p>
  @enderror


  <!-- 既存休憩 -->
  @foreach ($breaks as $index => $break)

    <div class="row">
      <div class="label">
        休憩{{ $index === 0 ? '' : $index + 1 }}
      </div>
      <div class="value">
        <div class="split">
          <input type="text"
            name="breaks[{{ $index }}][break_start]"
            value="{{ old("breaks.$index.break_start", optional($break->break_start)->format('H:i')) }}">
          〜
          <input type="text"
            name="breaks[{{ $index }}][break_end]"
            value="{{ old("breaks.$index.break_end", optional($break->break_end)->format('H:i')) }}">
        </div>
      </div>
    </div>

    @error("breaks.$index.break_start")
      <p class="error-message">{{ $message }}</p>
    @enderror
    @error("breaks.$index.break_end")
      <p class="error-message">{{ $message }}</p>
    @enderror

  @endforeach


  <!-- 新規休憩 -->
  @php $newIndex = $breaks->count(); @endphp

  <div class="row">
    <div class="label">
      休憩{{ $newIndex === 0 ? '' : $newIndex + 1 }}
    </div>
    <div class="value">
      <div class="split">
        <input type="text"
          name="breaks[{{ $newIndex }}][break_start]"
          value="{{ old("breaks.$newIndex.break_start") }}">
        〜
        <input type="text"
          name="breaks[{{ $newIndex }}][break_end]"
          value="{{ old("breaks.$newIndex.break_end") }}">
      </div>
    </div>
  </div>

  @error("breaks.$newIndex.break_start")
    <p class="error-message">{{ $message }}</p>
  @enderror
  @error("breaks.$newIndex.break_end")
    <p class="error-message">{{ $message }}</p>
  @enderror


  <!-- 備考 -->
  <div class="row">
    <div class="label">備考</div>
    <div class="value">
      <textarea name="remark">
{{ old('remark', $display->remark ?? '') }}
      </textarea>
    </div>
  </div>

  @error('remark')
    <p class="error-message">{{ $message }}</p>
  @enderror

</div>
  <div class="button-area">
    @if ($status === 'pending')
      <p class="pending-message">※承認待ちのため修正はできません。</p>
    @elseif ($status === 'approved')
      <span class="approved-btn">承認済み</span>
    @else
      <button type="submit" class="btn-black">修正</button>
    @endif
  </div>

  </form>
</main>

</body>
</html>