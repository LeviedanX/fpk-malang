<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Article;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $articles = Article::query()
            ->published()
            ->orderByDesc('published_at')
            ->get(['slug', 'updated_at']);

        $agendas = Agenda::query()
            ->published()
            ->orderByDesc('published_at')
            ->get(['slug', 'updated_at']);

        return response()
            ->view('public-site.sitemap', [
                'articles' => $articles,
                'agendas' => $agendas,
            ])
            ->header('Content-Type', 'application/xml');
    }
}
