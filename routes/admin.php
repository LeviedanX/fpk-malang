<?php

use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\AgendaController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GalleryImageController;
use App\Http\Controllers\Admin\ManagementMemberController;
use App\Http\Controllers\Admin\ManagementPeriodController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Middleware\EnsureAdminSettingsUnlocked;
use App\Http\Middleware\LogAdminActivity;
use App\Http\Middleware\SecureAdminSession;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', SecureAdminSession::class, LogAdminActivity::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        // Artikel
        Route::get('artikel/arsip', [ArticleController::class, 'archive'])->name('articles.archive');
        Route::patch('artikel/arsip/{article}/pulihkan', [ArticleController::class, 'restore'])->name('articles.restore');
        Route::delete('artikel/arsip/{article}/permanen', [ArticleController::class, 'forceDelete'])->name('articles.force-delete');
        Route::resource('artikel', ArticleController::class)
            ->parameters(['artikel' => 'article'])
            ->names('articles')
            ->except('show');

        // Agenda
        Route::get('agenda/riwayat', [AgendaController::class, 'history'])->name('agendas.history');
        Route::get('agenda/riwayat/{agenda}/log', [AgendaController::class, 'logs'])->name('agendas.logs');
        Route::delete('agenda/riwayat/{agenda}/permanen', [AgendaController::class, 'forceDelete'])->name('agendas.force-delete');
        Route::get('agenda/arsip', [AgendaController::class, 'archive'])->name('agendas.archive');
        Route::patch('agenda/arsip/{agenda}/pulihkan', [AgendaController::class, 'restore'])->name('agendas.restore');
        Route::resource('agenda', AgendaController::class)
            ->parameters(['agenda' => 'agenda'])
            ->names('agendas')
            ->except('show');

        // Galeri gambar
        Route::get('galeri', [GalleryImageController::class, 'index'])->name('galleries.index');
        Route::post('galeri', [GalleryImageController::class, 'store'])->name('galleries.store');
        Route::put('galeri', [GalleryImageController::class, 'update'])->name('galleries.update');
        Route::delete('galeri/{galleryImage}', [GalleryImageController::class, 'destroy'])->name('galleries.destroy');

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

        // Pengaturan Admin dilindungi PIN. Bookmark lama tetap dialihkan.
        Route::redirect('akun', '/admin/pengaturan-admin')->name('account.legacy');
        Route::get('pengaturan-admin', [AdminAccountController::class, 'edit'])->name('account.edit');
        Route::post('pengaturan-admin/buka', [AdminAccountController::class, 'unlock'])->name('account.unlock');
        Route::post('pengaturan-admin/pin', [AdminAccountController::class, 'setupPin'])->name('account.pin.setup');

        Route::middleware(EnsureAdminSettingsUnlocked::class)->group(function (): void {
            Route::put('pengaturan-admin/profil', [AdminAccountController::class, 'update'])->name('account.update');
            Route::put('pengaturan-admin/password', [AdminAccountController::class, 'updatePassword'])->name('account.password');
            Route::put('pengaturan-admin/pin', [AdminAccountController::class, 'updatePin'])->name('account.pin.update');
            Route::delete('pengaturan-admin/log-aktivitas', [AdminAccountController::class, 'clearActivityLogs'])->name('account.logs.clear');
        });
    });
