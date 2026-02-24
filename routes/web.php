<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', [LoginController::class, 'show'])->name('login');

Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/user/home', function (Request $request) {
        $user = $request->user();
        if ((int) ($user->user_type ?? 0) === 1) {
            return redirect()->route('admin.home');
        }

        return view('user.home');
    })->name('user.home');

    Route::get('/admin/home', function (Request $request) {
        $user = $request->user();
        if ((int) ($user->user_type ?? 0) !== 1) {
            abort(403);
        }

        return view('admin.home');
    })->name('admin.home');
});
