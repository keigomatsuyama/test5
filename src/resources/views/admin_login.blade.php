<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>管理者ログイン</title>
  <link rel="stylesheet" href="{{ asset('css/admin_login.css') }}">
</head>
<body>

<header class="header">
  <img src="{{ asset('images/logo.png') }}" alt="COACHTECHロゴ">
</header>

<main class="login-container">
  <h1 class="login-title">管理者ログイン</h1>

  <form class="login-form" method="POST" action="/admin/login" autocomplete="off">
    @csrf

    {{-- メールアドレス --}}
    <label>メールアドレス</label>
    <input
      type="email"
      name="email"
      value="{{ old('email') }}"
      class="@error('email') is-error @enderror"
    >
    @error('email')
      <div class="error-message">{{ $message }}</div>
    @enderror

    {{-- パスワード --}}
    <label>パスワード</label>
    <input
      type="password"
      name="password"
      class="@error('password') is-error @enderror"
    >
    @error('password')
      <div class="error-message">{{ $message }}</div>
    @enderror

    <button type="submit">管理者ログインする</button>
  </form>
</main>

</body>
</html>
