<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>勤怠詳細</title>
  <link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
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
  <h1 class="title">勤怠詳細</h1>

  {{-- ★ form は isEdit のときだけ、全部を包む --}}
  @if ($isEdit)
  <form method="POST" action="{{ url('/attendance/detail/'.$attendance->id) }}">
    @csrf
    @method('PUT')
  @endif

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
    <div class="row">
      <div class="label">出勤・退勤</div>
      <div class="value split">
        <input type="text" name="clock_in"
          value="{{ old('clock_in', optional($display->clock_in)->format('H:i')) }}"
          {{ $isEdit ? '' : 'readonly' }}>
        〜
        <input type="text" name="clock_out"
          value="{{ old('clock_out', optional($display->clock_out)->format('H:i')) }}"
          {{ $isEdit ? '' : 'readonly' }}>
      </div>
    </div>

    {{-- 出勤・退勤エラー（1回だけ表示） --}}
    @if ($errors->has('clock_in') || $errors->has('clock_out'))
      <p class="error-message">出勤時間もしくは退勤時間が不適切な値です</p>
    @endif

    @php
      $break1 = $display->breaks->get(0);
      $break2 = $display->breaks->get(1);
    @endphp

    {{-- 休憩1 --}}
    <div class="row">
      <div class="label">休憩1</div>
      <div class="value split">
        <input type="text" name="breaks[0][break_start]"
          value="{{ old('breaks.0.break_start', optional($break1?->break_start)->format('H:i')) }}"
          {{ $isEdit ? '' : 'readonly' }}>
        〜
        <input type="text" name="breaks[0][break_end]"
          value="{{ old('breaks.0.break_end', optional($break1?->break_end)->format('H:i')) }}"
          {{ $isEdit ? '' : 'readonly' }}>
      </div>
    </div>
    @error('breaks.0.break_start') <p class="error-message">{{ $message }}</p> @enderror
    @error('breaks.0.break_end')   <p class="error-message">{{ $message }}</p> @enderror

    {{-- 休憩2 --}}
    <div class="row">
      <div class="label">休憩2</div>
      <div class="value split">
        <input type="text" name="breaks[1][break_start]"
          value="{{ old('breaks.1.break_start', optional($break2?->break_start)->format('H:i')) }}"
          {{ $isEdit ? '' : 'readonly' }}>
        〜
        <input type="text" name="breaks[1][break_end]"
          value="{{ old('breaks.1.break_end', optional($break2?->break_end)->format('H:i')) }}"
          {{ $isEdit ? '' : 'readonly' }}>
      </div>
    </div>
    @error('breaks.1.break_start') <p class="error-message">{{ $message }}</p> @enderror
    @error('breaks.1.break_end')   <p class="error-message">{{ $message }}</p> @enderror

    {{-- 備考 --}}
    <div class="row">
      <div class="label">備考</div>
      <div class="value">
        <textarea name="remark" {{ $isEdit ? '' : 'readonly' }}>{{ old('remark', $display->remark) }}</textarea>
      </div>
    </div>
    @error('remark') <p class="error-message">{{ $message }}</p> @enderror

  </div>

  {{-- ボタン --}}
  <div class="button-area">
    @if ($isApproved)
      <span class="approved-btn">承認済み</span>

    @elseif ($isPending)
      <p class="pending-message">※承認待ちのため修正はできません。</p>

    @elseif ($isEdit)
      <button type="submit" class="btn-black">修正申請</button>

    @else
      <a href="{{ route('attendance.detail', $attendance->id) }}?edit=1"
         class="btn-black">修正</a>
    @endif
  </div>

  @if ($isEdit)
  </form>
  @endif

</main>
</body>
</html>
