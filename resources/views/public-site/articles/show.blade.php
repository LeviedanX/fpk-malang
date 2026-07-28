@extends('layouts.public')

@section('title', $article->meta_title ?: $article->title)
@section('meta_description', $article->meta_description ?: $article->excerpt)
@section('og_type', 'article')
@if ($article->thumbnail_path)
    @section('og_image', \Illuminate\Support\Facades\Storage::url($article->thumbnail_path))
@endif

@section('head')
    <meta property="article:published_time" content="{{ optional($article->published_at)->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ $article->updated_at->toIso8601String() }}">
    @php
        $articleImagePath = $article->thumbnail_path
            ? \Illuminate\Support\Facades\Storage::url($article->thumbnail_path)
            : null;
        $articleImageUrl = $articleImagePath
            ? (\Illuminate\Support\Str::startsWith($articleImagePath, ['http://', 'https://']) ? $articleImagePath : url($articleImagePath))
            : null;
        $articleJsonLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            '@id' => route('articles.show', $article).'#article',
            'mainEntityOfPage' => route('articles.show', $article),
            'headline' => $article->title,
            'description' => $article->meta_description ?: $article->excerpt,
            'image' => $articleImageUrl ? [$articleImageUrl] : null,
            'datePublished' => optional($article->published_at)->toIso8601String(),
            'dateModified' => $article->updated_at->toIso8601String(),
            'inLanguage' => 'id-ID',
            'publisher' => ['@id' => url('/').'#organization'],
        ], fn ($value) => filled($value));
        $breadcrumbJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Artikel', 'item' => route('articles.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $article->title, 'item' => route('articles.show', $article)],
            ],
        ];
    @endphp
    <script type="application/ld+json" nonce="{{ request()->attributes->get('csp_nonce') }}">{!! json_encode([$articleJsonLd, $breadcrumbJsonLd], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection

@section('content')
<article class="relative isolate overflow-hidden pb-16 pt-28 text-cream-50 sm:pt-32">
    <x-public-site.synced-background :profile="$profile" />

    <div class="container-x max-w-3xl!">
        <div class="rounded-2xl border border-white/15 bg-maroon-950/60 p-6 shadow-2xl shadow-black/25 backdrop-blur-md sm:p-8">
        <nav class="reveal reveal-left mb-6 text-sm font-medium text-cream-100/85" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-gold-300">Beranda</a>
            <span aria-hidden="true"> / </span>
            <a href="{{ route('articles.index') }}" class="hover:text-gold-300">Artikel</a>
        </nav>

        <header class="reveal">
            @if ($article->published_at)
                <time datetime="{{ $article->published_at->toDateString() }}" class="text-sm font-semibold uppercase tracking-wide text-gold-600">
                    {{ $article->published_at->translatedFormat('d F Y') }}
                </time>
            @endif
            <h1 class="mt-2 font-display text-3xl font-extrabold leading-tight text-cream-50 sm:text-4xl">{{ $article->title }}</h1>
        </header>

        @if ($article->thumbnail_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($article->thumbnail_path) }}" alt="{{ $article->title }}"
                 width="1200" height="675" fetchpriority="high" decoding="async"
                 class="reveal reveal-scale mt-8 aspect-video w-full rounded-xl object-cover shadow-sm">
        @endif

        <div class="public-detail-copy reveal prose prose-invert prose-lg mt-8 max-w-none prose-headings:font-display">
            {!! $article->body !!}
        </div>

        <div class="reveal mt-12 border-t border-white/15 pt-6">
            <a href="{{ route('articles.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-gold-300 hover:text-gold-200">
                <span aria-hidden="true">&larr;</span> Kembali ke daftar artikel
            </a>
        </div>
        </div>
    </div>
</article>
@endsection
