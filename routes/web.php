<?php

use App\Http\Controllers\PublicSite\AgendaController;
use App\Http\Controllers\PublicSite\ArticleController;
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site
|--------------------------------------------------------------------------
*/
Route::get('/', HomeController::class)->name('home');

Route::get('/artikel', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');

Route::get('/agenda', [AgendaController::class, 'index'])->name('agendas.index');
Route::get('/agenda/{agenda:slug}', [AgendaController::class, 'show'])->name('agendas.show');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

/*
|--------------------------------------------------------------------------
| Authentication & Admin
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
