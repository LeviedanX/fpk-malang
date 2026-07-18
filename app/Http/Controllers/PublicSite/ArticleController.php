<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        // Spotlight a featured article only on the unfiltered first page, and keep
        // it out of the paginated list so it is never shown twice.
        $featured = $search === '' ? Article::featuredForHome() : null;

        $articles = Article::query()
            ->select([
                'id', 'title', 'slug', 'excerpt', 'thumbnail_path',
                'is_featured', 'status', 'published_at',
            ])
            ->latestPublished()
            ->when($featured, fn ($query) => $query->whereKeyNot($featured->getKey()))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                });
            })
            ->paginate(config('fpk.pagination.articles_public'))
            ->withQueryString();

        return view('public-site.articles.index', [
            'articles' => $articles,
            'search' => $search,
            'featured' => $articles->onFirstPage() ? $featured : null,
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        return view('public-site.articles.show', [
            'article' => $article,
        ]);
    }
}
