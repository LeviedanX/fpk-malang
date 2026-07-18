<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use App\Support\HtmlSanitizer;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $articles = Article::query()
            ->select(['id', 'title', 'slug', 'is_featured', 'status', 'published_at', 'created_at'])
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when(
                in_array($status, [PublicationStatus::Draft->value, PublicationStatus::Published->value], true),
                fn ($query) => $query->where('status', $status)
            )
            ->orderByDesc('created_at')
            ->paginate(config('fpk.pagination.articles_admin'))
            ->withQueryString();

        return view('admin.articles.index', [
            'articles' => $articles,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.articles.create', [
            'article' => new Article(['status' => PublicationStatus::Published->value]),
        ]);
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $data = $this->buildData($request);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail_path'] = ImageStorage::store($request->file('thumbnail'), 'articles');
        }

        Article::create($data);

        return redirect()
            ->route('admin.articles.index')
            ->with('status', 'Artikel berhasil dibuat.');
    }

    public function edit(Article $article): View
    {
        return view('admin.articles.edit', [
            'article' => $article,
        ]);
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        $data = $this->buildData($request, $article);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail_path'] = ImageStorage::replace(
                $request->file('thumbnail'),
                $article->thumbnail_path,
                'articles'
            );
        }

        $article->update($data);

        return redirect()
            ->route('admin.articles.index')
            ->with('status', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('status', 'Artikel berhasil dihapus.');
    }

    /**
     * Assemble persisted attributes: sanitize the body and resolve publish time.
     *
     * @return array<string, mixed>
     */
    private function buildData(ArticleRequest $request, ?Article $article = null): array
    {
        $data = $request->safe()->except('thumbnail');
        $data['body'] = HtmlSanitizer::clean($request->input('body'));

        // Published articles need a publish timestamp; default to now if omitted.
        if ($data['status'] === PublicationStatus::Published->value && empty($data['published_at'])) {
            $data['published_at'] = $article?->published_at ?? now();
        }

        if ($data['status'] === PublicationStatus::Draft->value) {
            $data['published_at'] = $data['published_at'] ?? null;
        }

        return $data;
    }
}
