@props(['article'])

<article class="group surface card-lift flex h-full flex-col overflow-hidden">
    <a href="{{ route('articles.show', $article) }}" class="block aspect-video overflow-hidden bg-cream-100">
        @if ($article->thumbnail_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($article->thumbnail_path) }}" alt="{{ $article->title }}" width="640" height="360" loading="lazy" decoding="async" fetchpriority="low" class="h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105">
        @else
            <span class="flex h-full w-full items-center justify-center font-display text-3xl text-maroon-200" aria-hidden="true">FPK</span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-5">
        @if ($article->published_at)
            <time datetime="{{ $article->published_at->toDateString() }}" class="text-xs font-semibold uppercase tracking-wide text-gold-600">
                {{ $article->published_at->translatedFormat('d F Y') }}
            </time>
        @endif

        <h3 class="mt-2 font-display text-lg font-bold leading-snug text-ink-800">
            <a href="{{ route('articles.show', $article) }}" class="transition hover:text-maroon-700">{{ $article->title }}</a>
        </h3>

        @if ($article->excerpt)
            <p class="mt-2 line-clamp-3 text-sm font-medium leading-relaxed text-ink-700">{{ $article->excerpt }}</p>
        @endif

        <a href="{{ route('articles.show', $article) }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-maroon-700 transition group-hover:gap-2">
            Baca selengkapnya <span aria-hidden="true">&rarr;</span>
        </a>
    </div>
</article>
