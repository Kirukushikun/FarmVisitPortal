<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/user/home', function (Request $request) {
        $user = $request->user();
        if ((int) ($user->user_type ?? 0) === 1) {
            return redirect()->route('admin.home');
        }

        return view('user.home');
    })->name('user.home');

    Route::get('/user/change-password', function (Request $request) {
        $user = $request->user();
        if ((int) ($user->user_type ?? 0) === 1) {
            abort(403);
        }

        return view('auth.change-password-page');
    })->name('user.change-password');

    Route::get('/admin/home', function (Request $request) {
        $user = $request->user();
        if ((int) ($user->user_type ?? 0) !== 1) {
            abort(403);
        }

        return view('admin.home');
    })->name('admin.home');

    Route::get('/admin/users', function (Request $request) {
        $user = $request->user();
        if ((int) ($user->user_type ?? 0) !== 1) {
            abort(403);
        }

        return view('admin.users');
    })->name('admin.users');

    Route::get('/admin/locations', function (Request $request) {
        $user = $request->user();
        if ((int) ($user->user_type ?? 0) !== 1) {
            abort(403);
        }

        return view('admin.locations');
    })->name('admin.locations');

    Route::get('/admin/change-password', function (Request $request) {
        $user = $request->user();
        if ((int) ($user->user_type ?? 0) !== 1) {
            abort(403);
        }

        return view('auth.change-password-page');
    })->name('admin.change-password');
});
