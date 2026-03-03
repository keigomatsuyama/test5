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

  @if ($isEdit)
  <form method="POST" action="{{ url('/attendance/detail/'.$attendance->id) }}">
    @csrf
    @method('PUT')
  @endif

  <div class="card">

    <div class="row">
      <div class="label">名前</div>
      <div class="value">{{ $attendance->user->name }}</div>
    </div>

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

    {{-- ★ 出勤・退勤エラー完全対応版 --}}
    @if ($errors->has('clock_in') || $errors->has('clock_out'))
      <p class="error-message">
        {{ $errors->first('clock_in') ?: $errors->first('clock_out') }}
      </p>
    @endif

    @php
        $breaks = old('breaks', $display->breaks ?? []);

        if ($breaks instanceof \Illuminate\Support\Collection) {
            $breaks = $breaks->toArray();
        }

        $breaks = array_filter($breaks, function($break) {
            return !empty($break['break_start']) || !empty($break['break_end']);
        });

        $breaks = array_values($breaks);
    @endphp

    @foreach ($breaks as $index => $break)
    <div class="row">
      <div class="label">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</div>
      <div class="value split">
        <input type="text"
          name="breaks[{{ $index }}][break_start]"
          value="{{ old("breaks.$index.break_start",
            isset($display->breaks[$index])
              ? optional($display->breaks[$index]->break_start)->format('H:i')
              : ''
          ) }}"
          {{ $isEdit ? '' : 'readonly' }}>

        〜

        <input type="text"
          name="breaks[{{ $index }}][break_end]"
          value="{{ old("breaks.$index.break_end",
            isset($display->breaks[$index])
              ? optional($display->breaks[$index]->break_end)->format('H:i')
              : ''
          ) }}"
          {{ $isEdit ? '' : 'readonly' }}>
      </div>
    </div>

    @error("breaks.$index.break_start")
      <p class="error-message">{{ $message }}</p>
    @enderror

    @error("breaks.$index.break_end")
      <p class="error-message">{{ $message }}</p>
    @enderror
    @endforeach

    @php $newIndex = count($breaks); @endphp

    <div class="row">
      <div class="label">
        {{ $newIndex === 0 ? '休憩' : '休憩' . ($newIndex + 1) }}
      </div>
      <div class="value split">
        <input type="text"
          name="breaks[{{ $newIndex }}][break_start]"
          value="{{ old("breaks.$newIndex.break_start") }}"
          {{ $isEdit ? '' : 'readonly' }}>

        〜

        <input type="text"
          name="breaks[{{ $newIndex }}][break_end]"
          value="{{ old("breaks.$newIndex.break_end") }}"
          {{ $isEdit ? '' : 'readonly' }}>
      </div>
    </div>

    <div class="row">
      <div class="label">備考</div>
      <div class="value">
        <textarea name="remark" {{ $isEdit ? '' : 'readonly' }}>{{ old('remark', $display->remark) }}</textarea>
      </div>
    </div>

    @error('remark')
      <p class="error-message">{{ $message }}</p>
    @enderror

  </div>

  <div class="button-area">
    @if ($isApproved)
        <span class="approved-btn">承認済み</span>
    @elseif ($isPending)
        <p class="pending-message">※承認待ちのため修正はできません。</p>
    @else
        <button type="submit" class="btn-black">修正</button>
    @endif
  </div>

  @if ($isEdit)
  </form>
  @endif

</main>
</body>
</html>