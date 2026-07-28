<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Middleware\EnsureDesktopBrowser;
use Illuminate\Support\Facades\Route;

// Gerbang desktop dipasang di sini juga supaya ponsel tidak pernah sampai ke
// formulir password—bukan baru ditolak setelah berhasil masuk.
Route::middleware(EnsureDesktopBrowser::class)->group(function (): void {
    Route::middleware('guest')->group(function () {
        Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
            ->name('login.store');
    });

    Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');
});
