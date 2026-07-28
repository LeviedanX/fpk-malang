<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use App\Support\HtmlSanitizer;
use App\Support\MediaTransaction;
use App\Support\SearchTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $search = SearchTerm::fromRequest($request);
        $pattern = SearchTerm::likePattern($search);
        $status = $request->query('status');

        $articles = Article::query()
            ->select(['id', 'title', 'slug', 'is_featured', 'status', 'published_at', 'created_at'])
            ->when($search !== '', fn ($query) => $query->where('title', 'like', $pattern))
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

    public function archive(Request $request): View
    {
        $search = SearchTerm::fromRequest($request);
        $pattern = SearchTerm::likePattern($search);

        $articles = Article::onlyTrashed()
            ->select(['id', 'title', 'slug', 'thumbnail_path', 'deleted_at'])
            ->when($search !== '', fn ($query) => $query->where('title', 'like', $pattern))
            ->orderByDesc('deleted_at')
            ->paginate(config('fpk.pagination.articles_admin'))
            ->withQueryString();

        return view('admin.articles.archive', [
            'articles' => $articles,
            'search' => $search,
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
        $media = new MediaTransaction;

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail_path'] = $media->storeImage($request->file('thumbnail'), 'articles');
        }

        $media->commit(fn () => Article::create($data));

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
        $media = new MediaTransaction;

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail_path'] = $media->replaceImage(
                $request->file('thumbnail'),
                $article->thumbnail_path,
                'articles'
            );
        }

        $media->commit(fn () => $article->update($data));

        return redirect()
            ->route('admin.articles.index')
            ->with('status', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('status', 'Artikel berhasil diarsipkan dan masih dapat dipulihkan.');
    }

    public function restore(string $article): RedirectResponse
    {
        $archivedArticle = $this->findArchived($article);
        $archivedArticle->restore();

        return redirect()
            ->route('admin.articles.archive')
            ->with('status', 'Artikel berhasil dipulihkan.');
    }

    public function forceDelete(string $article): RedirectResponse
    {
        $archivedArticle = $this->findArchived($article);
        $media = new MediaTransaction;
        $media->deleteAfterCommit($archivedArticle->thumbnail_path);

        $media->commit(fn () => $archivedArticle->forceDelete());

        return redirect()
            ->route('admin.articles.archive')
            ->with('status', 'Artikel dan gambar sampulnya berhasil dihapus permanen.');
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

    private function findArchived(string $slug): Article
    {
        return Article::onlyTrashed()
            ->where('slug', $slug)
            ->firstOrFail();
    }
}
