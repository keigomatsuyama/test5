<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>勤怠詳細</title>
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
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
      @csrf
    </form>
  </nav>
</header>

<main class="container">
  <h1 class="title">｜勤怠詳細</h1>

  {{-- 成功メッセージ --}}
  @if(session('success'))
    <div class="alert-success">
      {{ session('success') }}
    </div>
  @endif

  <form method="POST" action="{{ route('admin.attendances.update', $attendance->id) }}">
    @csrf
    @method('PATCH')
    <input type="hidden" name="redirect_to" value="{{ url()->previous() }}">

    <div class="detail-card">
      <table class="detail-table">

        <tr>
          <th>名前</th>
          <td colspan="3">{{ $attendance->user->name }}</td>
        </tr>

        <tr>
          <th>日付</th>
          <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</td>
          <td colspan="2">{{ \Carbon\Carbon::parse($attendance->date)->format('m月d日') }}</td>
        </tr>

        {{-- 出勤・退勤 --}}
        <tr>
          <th>出勤・退勤</th>
          <td>
            <input type="time" name="clock_in"
                   value="{{ old('clock_in', $attendance->clock_in) }}">
            @error('clock_in')
              <div class="error-message">{{ $message }}</div>
            @enderror
          </td>
          <td class="center">〜</td>
          <td>
            <input type="time" name="clock_out"
                   value="{{ old('clock_out', $attendance->clock_out) }}">
            @error('clock_out')
              <div class="field-error">{{ $message }}</div>
            @enderror
          </td>
        </tr>

        {{-- 休憩1 --}}
        @php $break1 = $attendance->breaks->get(0); @endphp
        <tr>
          <th>休憩</th>
          <td>
            <input type="time" name="breaks[0][break_start]"
                   value="{{ old('breaks.0.break_start', $break1?->break_start) }}">
            @error('breaks.0.break_start')
              <div class="error-message">{{ $message }}</div>
            @enderror
          </td>
          <td class="center">〜</td>
          <td>
            <input type="time" name="breaks[0][break_end]"
                   value="{{ old('breaks.0.break_end', $break1?->break_end) }}">
            @error('breaks.0.break_end')
              <div class="error-message">{{ $message }}</div>
            @enderror
          </td>
        </tr>

        {{-- 休憩2 --}}
        @php $break2 = $attendance->breaks->get(1); @endphp
        <tr>
          <th>休憩2</th>
          <td>
            <input type="time" name="breaks[1][break_start]"
                   value="{{ old('breaks.1.break_start', $break2?->break_start) }}">
            @error('breaks.1.break_start')
              <div class="error-message">{{ $message }}</div>
            @enderror
          </td>
          <td class="center">〜</td>
          <td>
            <input type="time" name="breaks[1][break_end]"
                   value="{{ old('breaks.1.break_end', $break2?->break_end) }}">
            @error('breaks.1.break_end')
              <div class="error-message">{{ $message }}</div>
            @enderror
          </td>
        </tr>

        {{-- 備考 --}}
        <tr>
          <th>備考</th>
          <td colspan="3">
            <textarea name="remark">{{ old('remark', $attendance->remark) }}</textarea>
            @error('remark')
              <div class="error-message">{{ $message }}</div>
            @enderror
          </td>
        </tr>

      </table>
    </div>

    <div class="button-area">
      <button type="submit" class="edit-btn">修正</button>
    </div>

  </form>
</main>

</body>
</html>
