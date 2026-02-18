<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Actions\Fortify\CreateNewUser;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ログイン画面
        Fortify::loginView(fn () => view('auth.login'));

        // 会員登録画面
        Fortify::registerView(fn () => view('auth.register'));

        // ★ メール認証画面（ここが本命）
        Fortify::verifyEmailView(fn () => view('auth.verify'));

        // ユーザー作成処理
        Fortify::createUsersUsing(CreateNewUser::class);

        // ログイン制限
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->email.$request->ip());
        });
    }
}
