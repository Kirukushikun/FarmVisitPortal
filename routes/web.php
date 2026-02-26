<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('guest.landing');
})->name('landing');

Route::get('/login/user', [AuthController::class, 'showUserLogin'])->name('login.user');

Route::get('/login/admin', [AuthController::class, 'showAdminLogin'])->name('login.admin');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/user/home', [PortalController::class, 'userHome'])->name('user.home');

    Route::get('/user/change-password', [PortalController::class, 'userChangePassword'])->name('user.change-password');

    Route::get('/admin/home', [PortalController::class, 'adminHome'])->name('admin.home');

    Route::get('/admin/users', [PortalController::class, 'adminUsers'])->name('admin.users');

    Route::get('/admin/locations', [PortalController::class, 'adminLocations'])->name('admin.locations');

    Route::get('/admin/change-password', [PortalController::class, 'adminChangePassword'])->name('admin.change-password');
});
