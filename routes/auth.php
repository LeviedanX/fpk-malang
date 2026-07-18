<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Middleware\EnsureDesktopAdminAccess;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureDesktopAdminAccess::class, 'guest'])->group(function () {
    Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');
});

Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware([EnsureDesktopAdminAccess::class, 'auth'])
    ->name('logout');
