<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileRequest;
use Symfony\Component\HttpKernel\Profiler\Profiler;

class UserController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_admin' => 0,
        ]);

        Auth::login($user);

        return redirect('/attendance');
    }
public function login(LoginRequest $request)
{
    $credentials = $request->only('email', 'password');

    // ★ 一般ユーザーのみ許可
    $credentials['is_admin'] = false;

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect('/attendance');
    }

    return back()->withErrors([
        'password' => 'ログイン情報が登録されていません。',
    ]);
}

}
