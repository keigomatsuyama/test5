<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>勤怠一覧</title>
  <link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}">
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

  <!-- メイン -->
  <main class="container">
    <h1 class="title">｜ スタッフ一覧</h1>

    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>名前</th>
            <th>メールアドレス</th>
            <th>月次勤怠</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($users as $user)
            <tr>
              <td>{{ $user->name }}</td>
              <td >
                {{ $user->email }}
              </td>
              <td>
               <a
  href="{{ route('admin.attendance.staff', $user->id) }}"
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
  </main>

</body>
</html>
