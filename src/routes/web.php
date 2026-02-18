<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StampController;
use App\Http\Controllers\AdminStampController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 認証（ログイン・登録）
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    return view('auth.login');
});
Route::post('/login', [UserController::class, 'login']);

Route::get('/register', function () {
    return view('auth.register');
});
Route::post('/register', [UserController::class, 'register']);

/*
|--------------------------------------------------------------------------
| ログアウト
|--------------------------------------------------------------------------
*/
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| 🔐 メール認証必須（ユーザー）
|--------------------------------------------------------------------------
| ここが最重要ポイント
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // マイページ
    Route::get('/mypage', [UserController::class, 'mypage']);

    // 勤怠
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

    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])
        ->name('attendance.detail');

    Route::put('/attendance/detail/{id}', [AttendanceController::class, 'update'])
        ->name('attendance.update');

    // スタンプ申請（一般ユーザー）
    Route::get(
        '/stamp_correction_request/list',
        [StampController::class, 'index']
    )->name('stamp.index');

    Route::get(
        '/stamp_correction_request/{id}',
        [StampController::class, 'show']
    )->name('stamp.show');
});

/*
|--------------------------------------------------------------------------
| 管理者
|--------------------------------------------------------------------------
*/
Route::get('/admin/login', [AdminController::class, 'adminlogin']);
Route::post('/admin/login', [AdminController::class, 'login']);

Route::prefix('admin')
    ->middleware(['auth:admin', 'admin'])
    ->group(function () {

        Route::get('/attendance/list', [AdminController::class, 'index'])
            ->name('admin.attendances.index');

        Route::get('/attendance/{attendance}', [AdminController::class, 'detail'])
            ->name('admin.attendances.detail');

        Route::patch('/attendance/{attendance}', [AdminController::class, 'update'])
            ->name('admin.attendances.update');

        Route::get('/staff/list', [AdminController::class, 'list'])
            ->name('admin.staff.list');

        Route::get('/attendance/staff/{id}', [AdminController::class, 'staff'])
            ->name('admin.attendance.staff');
        Route::get('/attendance/staff/{id}/csv', [AdminController::class, 'export'])
            ->name('admin.attendance.csv');

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
