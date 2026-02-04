<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>申請一覧</title>
  <link rel="stylesheet" href="{{ asset('css/attendance_stamp.css') }}">
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
    <h1 class="title">申請一覧</h1>

    <!-- タブ -->
    <div class="tab-menu">
      <button class="tab active" id="tab-pending">承認待ち</button>
      <button class="tab" id="tab-approved">承認済み</button>
    </div>

    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
          </tr>
        </thead>

        <!-- 承認待ち -->
        <tbody id="pending">
          @forelse ($pendingRequests as $request)
          <tr>
            <td>承認待ち</td>
            <td>{{ $request->user->name }}</td>
            <td>{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}</td>
            <td>{{ $request->remark }}</td>
            <td>{{ $request->created_at->format('Y/m/d') }}</td>
            <td>
              <a href="{{ route('attendance.detail', $request->attendance->id) }}">
                詳細
              </a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6">承認待ちの申請はありません</td>
          </tr>
          @endforelse
        </tbody>

        <!-- 承認済み -->
        <tbody id="approved" style="display:none;">
          @forelse ($approvedRequests as $request)
          <tr>
            <td>承認済み</td>
            <td>{{ $request->user->name }}</td>
            <td>{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}</td>
            <td>{{ $request->remark }}</td>
            <td>{{ $request->created_at->format('Y/m/d') }}</td>
            <td>
              <a href="{{ route('attendance.detail', $request->attendance->id) }}">
                詳細
              </a>

            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6">承認済みの申請はありません</td>
          </tr>
          @endforelse
        </tbody>

      </table>
    </div>
  </main>

  <script>
    const tabPending = document.getElementById('tab-pending');
    const tabApproved = document.getElementById('tab-approved');
    const pendingBody = document.getElementById('pending');
    const approvedBody = document.getElementById('approved');

    tabPending.onclick = () => {
      tabPending.classList.add('active');
      tabApproved.classList.remove('active');
      pendingBody.style.display = '';
      approvedBody.style.display = 'none';
    };

    tabApproved.onclick = () => {
      tabApproved.classList.add('active');
      tabPending.classList.remove('active');
      pendingBody.style.display = 'none';
      approvedBody.style.display = '';
    };
  </script>

</body>

</html>