<?php

use App\Http\Controllers\PublicSite\AgendaController;
use App\Http\Controllers\PublicSite\ArticleController;
use App\Http\Controllers\PublicSite\ChatController;
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\RobotsController;
use App\Http\Controllers\PublicSite\SitemapController;
use App\Http\Controllers\ReadinessController;
use App\Http\Middleware\PublicCacheHeaders;
use App\Http\Middleware\ResolveChatGuest;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/*
|--------------------------------------------------------------------------
| Public site
|--------------------------------------------------------------------------
*/
Route::middleware(PublicCacheHeaders::class)
    ->withoutMiddleware([
        StartSession::class,
        ShareErrorsFromSession::class,
        PreventRequestForgery::class,
    ])
    ->group(function (): void {
        Route::get('/', HomeController::class)->name('home');

        Route::get('/artikel', [ArticleController::class, 'index'])->name('articles.index');
        Route::get('/artikel/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');

        // Daftar agenda kini tampil di beranda (#agenda); tautan lama dialihkan.
        Route::redirect('/agenda', '/#agenda')->name('agendas.index');
        Route::get('/agenda/{agenda:slug}', [AgendaController::class, 'show'])->name('agendas.show');

        Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
        Route::get('/robots.txt', RobotsController::class)->name('robots');
    });

// Tujuan pengalihan bagi iPad dalam mode desktop: perangkat itu mengirim user
// agent "Macintosh" tanpa client hint, sehingga hanya bisa dikenali di browser.
Route::view('/perangkat-tidak-didukung', 'errors.desktop-only')
    ->withoutMiddleware([
        StartSession::class,
        ShareErrorsFromSession::class,
        PreventRequestForgery::class,
    ])
    ->name('desktop-only');

/*
|--------------------------------------------------------------------------
| Chat tamu (stateless)
|--------------------------------------------------------------------------
|
| Sengaja tidak memakai sesi maupun CSRF: kredensialnya adalah token acak per
| percakapan yang hanya hidup di localStorage, sehingga browser tidak pernah
| melampirkannya pada permintaan lintas situs. ResolveChatGuest menerjemahkan
| token itu menjadi percakapan dan memasang header no-store.
|
*/
Route::middleware(ResolveChatGuest::class)
    ->withoutMiddleware([
        StartSession::class,
        ShareErrorsFromSession::class,
        PreventRequestForgery::class,
    ])
    ->prefix('chat')
    ->name('chat.')
    ->group(function (): void {
        Route::get('/', [ChatController::class, 'show'])
            ->middleware('throttle:chat-read')
            ->name('show');

        Route::get('/poll', [ChatController::class, 'poll'])
            ->middleware('throttle:chat-poll')
            ->name('poll');

        Route::get('/history', [ChatController::class, 'history'])
            ->middleware('throttle:chat-read')
            ->name('history');

        Route::post('/', [ChatController::class, 'store'])
            ->middleware('throttle:chat-send')
            ->name('store');
    });

/*
|--------------------------------------------------------------------------
| Authentication & Admin
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';

Route::get('/ready', ReadinessController::class)
    ->withoutMiddleware([
        StartSession::class,
        ShareErrorsFromSession::class,
        PreventRequestForgery::class,
    ])
    ->name('readiness');
