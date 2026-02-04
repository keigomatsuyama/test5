<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StampController;
use App\Http\Controllers\AdminStampController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
//ログイン関係//
Route::get('/login', function () {
    return view('auth.login'); // ← 正解
});
Route::post('/login', [UserController::class, 'login']);
Route::get('/register', function () {
    return view('auth.register'); // ← 正解
});
Route::post('/register', [UserController::class, 'register']);
//メール認証付きマイページ//
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mypage', [UserController::class, 'mypage']);
});

//ユーザー用//
Route::middleware('auth')->group(function () {

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
        ->name('attendance.clockIn');

    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])
        ->name('attendance.breakIn');

    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])
        ->name('attendance.breakOut');

    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
        ->name('attendance.clockOut');
    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/login');
    })->middleware('auth')->name('logout');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])
        ->name('attendance.detail');
    Route::put('/attendance/detail/{id}', [AttendanceController::class, 'update'])
        ->name('attendance.update');
});

//管理者用//
Route::get('/admin/login', [AdminController::class, 'adminlogin']);
Route::post('/admin/login', [AdminController::class, 'login']);
Route::get('/admin/attendance/list', [AdminController::class, 'index'])->name('admin.attendances.index');
Route::get('/admin/attendance/{attendance}', [AdminController::class, 'detail'])
    ->name('admin.attendances.detail');
Route::get('/admin/staff/list', [AdminController::class, 'list'])
    ->name('admin.staff.list');
Route::get(
    '/admin/attendance/staff/{id}',
    [AdminController::class, 'staff']
)->name('admin.attendance.staff');

// スタンプ修正申請一覧（一般・管理者 共通）
// 一般ユーザー
Route::middleware('auth')->group(function () {
    Route::get(
        '/stamp_correction_request/list',
        [StampController::class, 'index']
    )->name('stamp.index');
      Route::get(
        '/stamp_correction_request/{id}',
        [StampController::class, 'show']
    )->name('stamp.show');
});

// 管理者
Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->group(function () {

        Route::get(
            '/stamp_correction_request/list',
            [AdminStampController::class, 'index']
        )->name('admin.stamp.index');
 Route::get(
    '/stamp_correction_request/approve/{attendance_correct_request_id}',
    [AdminStampController::class, 'show']
)->name('admin.stamp.show');
Route::post(
    '/stamp_correction_request/approve/{attendance_correct_request_id}',
    [AdminStampController::class, 'approve']
)->name('admin.stamp.approve');
    });
Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->group(function () {

        Route::patch(
            '/admin/attendance/{attendance}',
            [AdminController::class, 'update']
        )->name('admin.attendances.update');
    });