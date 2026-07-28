@extends('layouts.public')

@section('title', $search !== '' ? 'Hasil pencarian artikel' : 'Artikel')
@section('meta_description', 'Kumpulan artikel dan informasi '.$site->organization_name.'.')
@if ($search !== '')
    @section('robots', 'noindex, follow')
    @section('canonical', route('articles.index'))
@elseif ($articles->currentPage() > 1)
    @section('canonical', route('articles.index', ['page' => $articles->currentPage()]))
@endif

@section('pagination_links')
    @if ($articles->previousPageUrl())
        <link rel="prev" href="{{ $articles->previousPageUrl() }}">
    @endif
    @if ($articles->nextPageUrl())
        <link rel="next" href="{{ $articles->nextPageUrl() }}">
    @endif
@endsection

@section('content')
<div class="relative isolate overflow-hidden" data-articles-unified-background>
<x-public-site.synced-background :profile="$profile" />

<x-public-site.page-header
    eyebrow="Kabar & Informasi"
    title="Artikel"
    subtitle="Informasi, kegiatan, dan wawasan seputar pembauran kebangsaan di Kota Malang."
    :back-href="route('home')"
    back-label="Kembali ke Beranda"
    :profile="$profile"
    :show-background="false" />

<section class="section bg-transparent">
    <div class="container-x">
        <form method="GET" action="{{ route('articles.index') }}" class="reveal reveal-scale mx-auto mb-10 flex max-w-xl gap-2">
            <label for="q" class="sr-only">Cari artikel</label>
            <input type="search" name="q" id="q" value="{{ $search }}" placeholder="Cari artikel..." maxlength="100"
                class="block w-full rounded-lg border-ink-200 bg-white shadow-sm focus:border-maroon-600 focus:ring-maroon-600">
            <button type="submit" class="btn-primary">Cari</button>
        </form>

        @if ($featured)
            <article class="reveal group surface card-lift mb-10 grid overflow-hidden lg:grid-cols-2">
                <a href="{{ route('articles.show', $featured) }}" class="relative block aspect-16/10 overflow-hidden bg-cream-100 lg:aspect-auto">
                    @if ($featured->thumbnail_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($featured->thumbnail_path) }}"
                             alt="{{ $featured->title }}" width="1280" height="800"
                             fetchpriority="high" decoding="async"
                             class="h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105">
                    @else
                        <span class="flex h-full w-full items-center justify-center py-16 font-display text-5xl text-maroon-200" aria-hidden="true">FPK</span>
                    @endif
                    <span class="absolute left-4 top-4 inline-flex items-center gap-1 rounded-full bg-gold-500 px-3 py-1 text-xs font-semibold text-maroon-950 shadow-sm">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.8 6.2 20.9l1.1-6.5L2.6 9.8l6.5-.9z"/></svg>
                        Unggulan
                    </span>
                </a>
                <div class="flex flex-col justify-center p-6 sm:p-8">
                    @if ($featured->published_at)
                        <time datetime="{{ $featured->published_at->toDateString() }}" class="text-xs font-semibold uppercase tracking-wide text-gold-600">
                            {{ $featured->published_at->translatedFormat('d F Y') }}
                        </time>
                    @endif
                    <h2 class="mt-2 font-display text-2xl font-bold leading-snug text-ink-800 sm:text-3xl">
                        <a href="{{ route('articles.show', $featured) }}" class="transition hover:text-maroon-700">{{ $featured->title }}</a>
                    </h2>
                    @if ($featured->excerpt)
                        <p class="mt-3 line-clamp-3 text-sm font-medium leading-relaxed text-ink-700">{{ $featured->excerpt }}</p>
                    @endif
                    <a href="{{ route('articles.show', $featured) }}" class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-maroon-700 transition group-hover:gap-2">
                        Baca selengkapnya <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </article>
        @endif

        @if ($articles->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <div class="reveal" style="--reveal-delay: {{ ($loop->index % 3) * 80 }}ms">
                        <x-public-site.article-card :article="$article" />
                    </div>
                @endforeach
            </div>

            <div class="reveal mt-10">{{ $articles->links() }}</div>
        @elseif (! $featured)
            <x-public-site.empty-state>
                @if ($search !== '')
                    Tidak ada artikel yang cocok dengan pencarian "<span class="font-medium">{{ $search }}</span>".
                    <a href="{{ route('articles.index') }}" class="mt-2 block text-maroon-700 hover:underline">Tampilkan semua artikel</a>
                @else
                    Belum ada artikel yang dipublikasikan.
                @endif
            </x-public-site.empty-state>
        @endif
    </div>
</section>
</div>
@endsection
