<?php

use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\AgendaController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManagementMemberController;
use App\Http\Controllers\Admin\ManagementPeriodController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Middleware\EnsureDesktopAdminAccess;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureDesktopAdminAccess::class, 'auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        // Artikel
        Route::resource('artikel', ArticleController::class)
            ->parameters(['artikel' => 'article'])
            ->names('articles')
            ->except('show');

        // Agenda
        Route::resource('agenda', AgendaController::class)
            ->parameters(['agenda' => 'agenda'])
            ->names('agendas')
            ->except('show');

        // Susunan Pengurus
        Route::resource('pengurus/periode', ManagementPeriodController::class)
            ->parameters(['periode' => 'period'])
            ->names('periods')
            ->except('show');

        Route::resource('pengurus/anggota', ManagementMemberController::class)
            ->parameters(['anggota' => 'member'])
            ->names('members')
            ->except('show');

        // Foto bersama periode (dikelola dari halaman Anggota)
        Route::put('pengurus/foto-bersama/{period}', [ManagementPeriodController::class, 'updateGroupPhoto'])
            ->name('members.group_photo');
        Route::delete('pengurus/foto-bersama/{period}', [ManagementPeriodController::class, 'destroyGroupPhoto'])
            ->name('members.group_photo.destroy');

        // Pengaturan Website (singleton)
        Route::get('pengaturan', [SiteSettingController::class, 'edit'])->name('settings.edit');
        Route::put('pengaturan', [SiteSettingController::class, 'update'])->name('settings.update');

        // Bookmark lama tetap aman, tetapi seluruh pengelolaan diarahkan ke
        // halaman Pengaturan Website yang terpadu.
        Route::get('profil', fn () => redirect(route('admin.settings.edit').'#tentang'))
            ->name('profile.edit');
        Route::get('kontak', fn () => redirect(route('admin.settings.edit').'#kontak'))
            ->name('contact.edit');

        // Akun Admin
        Route::get('akun', [AdminAccountController::class, 'edit'])->name('account.edit');
        Route::put('akun', [AdminAccountController::class, 'update'])->name('account.update');
        Route::put('akun/password', [AdminAccountController::class, 'updatePassword'])->name('account.password');
    });
