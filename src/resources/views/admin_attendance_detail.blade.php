<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>勤怠詳細（管理者）</title>
  <link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
</head>
<body>

<header class="header">
  <img src="{{ asset('images/logo.png') }}" alt="ロゴ">
  <nav class="nav">
    <a href="{{ route('admin.attendances.index') }}">勤怠一覧</a>
    <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
    <a href="{{ route('admin.stamp.index') }}">申請一覧</a>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button class="logout-link">ログアウト</button>
    </form>
  </nav>
</header>

<main class="container">
  <h1 class="title">勤怠詳細</h1>

  <form method="POST" action="{{ route('admin.attendances.update', $attendance->id) }}">
    @csrf
    @method('PATCH')

@php
    $latestRequest = $attendance->attendanceRequests
        ->sortByDesc('id')
        ->first();

    $display = $latestRequest ?? $attendance;

    $break1 = $display->breaks->get(0);
    $break2 = $display->breaks->get(1);
        $isPending = $latestRequest && $latestRequest->status === 'pending';
            $status = $latestRequest->status ?? null;
@endphp


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
     <div class="row">
  <div class="label">出勤・退勤</div>
  <div class="value">
    <div class="split">
      <input type="text" name="clock_in"
       value="{{ old('clock_in', optional($display->clock_in)->format('H:i')) }}"
>
      <span>〜</span>
      <input type="text" name="clock_out"
        value="{{ old('clock_out', optional($display->clock_out)->format('H:i')) }}">
    </div>
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


      {{-- 休憩 --}}
     <div class="row">
  <div class="label">休憩1</div>
  <div class="value">
    <div class="split">
      <input type="text" name="breaks[0][break_start]"value="{{ old('breaks.0.break_start', optional($break1?->break_start)->format('H:i')) }}"
>
      <span>〜</span>
      <input type="text" name="breaks[0][break_end]"
        value="{{ old('breaks.0.break_end', optional($break1?->break_end)->format('H:i')) }}">
    </div>
  </div>
</div>
 @error('breaks.0.break_start') <p class="error-message">{{ $message }}</p> @enderror
    @error('breaks.0.break_end')   <p class="error-message">{{ $message }}</p> @enderror

      {{-- 休憩2 --}}
      <div class="row">
        <div class="label">休憩2</div>
        <div class="value split">
          <input type="text" name="breaks[1][break_start]"
            value="{{ old('breaks.1.break_start', optional($break2?->break_start)->format('H:i')) }}">
          〜
          <input type="text" name="breaks[1][break_end]"
            value="{{ old('breaks.1.break_end', optional($break2?->break_end)->format('H:i')) }}">
        </div>
      </div>
 @error('breaks.1.break_start') <p class="error-message">{{ $message }}</p> @enderror
    @error('breaks.1.break_end')   <p class="error-message">{{ $message }}</p> @enderror
      {{-- 備考 --}}
      <div class="row">
  <div class="label">備考</div>

  <div class="value">
<textarea name="remark"
    {{ in_array($status, ['pending', 'approved']) ? 'readonly' : '' }}>
    {{ old('remark', $display->remark ?? '') }}
</textarea>
  </div>
</div>
    @error('remark')
      <p class="error-message">{{ $message }}</p>
    @enderror
  </div>
</div>

 <div class="button-area">

    @if ($status === 'pending')
        <p class="pending-message">
            ※承認待ちのため修正はできません。
        </p>

    @elseif ($status === 'approved')
        <span class="approved-btn">
            承認済み
        </span>

    @else
        <button type="submit" class="btn-black">
            修正
        </button>
    @endif

</div>


  <div class="button-area">
  </form>
</main>
  <div class="button-area">
</body>
</html>
