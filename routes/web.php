<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('guest.landing');
})->name('landing');

Route::get('/login', function () {
    return redirect()->route('landing');
})->name('login');

Route::get('/login/user', [AuthController::class, 'showUserLogin'])->name('login.user');

Route::get('/login/admin', [AuthController::class, 'showAdminLogin'])->name('login.admin');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth', 'no-cache'])->name('logout');

Route::middleware(['auth', 'no-cache'])->group(function () {
    Route::get('/user/home', [PortalController::class, 'userHome'])->name('user.home');

    Route::get('/user/change-password', [PortalController::class, 'userChangePassword'])->name('user.change-password');

    Route::get('/user/permits/scheduled', [PortalController::class, 'userScheduledPermits'])->name('user.permits.scheduled');

    Route::get('/user/permits/my-permits', [PortalController::class, 'userMyPermits'])->name('user.permits.my');

    Route::get('/user/permits/cancelled', [PortalController::class, 'userCancelledPermits'])->name('user.permits.cancelled');

    Route::get('/user/permits/{permit}', [PortalController::class, 'userShowPermit'])->name('user.permits.show');

    Route::post('/user/permits/{permit}/complete', [PortalController::class, 'completePermit'])->name('user.permits.complete');

    Route::post('/user/permits/{permit}/cancel', [PortalController::class, 'cancelPermit'])->name('user.permits.cancel');

    Route::get('/admin/home', [PortalController::class, 'adminHome'])->name('admin.home');

    Route::get('/admin/users', [PortalController::class, 'adminUsers'])->name('admin.users');

    Route::get('/admin/locations', [PortalController::class, 'adminLocations'])->name('admin.locations');

    Route::get('/admin/permits', [PortalController::class, 'adminPermits'])->name('admin.permits.index');

    Route::get('/admin/permits/create', [PortalController::class, 'adminCreatePermit'])->name('admin.permits.create');

    Route::get('/admin/permits/{permit}/edit', [PortalController::class, 'adminEditPermit'])->name('admin.permits.edit');

    Route::get('/admin/permits/{permit}', [PortalController::class, 'adminShowPermit'])->name('admin.permits.show');

    Route::get('/admin/change-password', [PortalController::class, 'adminChangePassword'])->name('admin.change-password');
});
