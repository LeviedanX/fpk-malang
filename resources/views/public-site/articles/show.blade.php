@extends('layouts.public')

@section('title', $article->meta_title ?: $article->title)
@section('meta_description', $article->meta_description ?: $article->excerpt)

@section('head')
    @if ($article->thumbnail_path)
        <meta property="og:image" content="{{ \Illuminate\Support\Facades\Storage::url($article->thumbnail_path) }}">
    @endif
    <meta property="article:published_time" content="{{ optional($article->published_at)->toIso8601String() }}">
@endsection

@section('content')
<article class="bg-white pb-16 pt-28 dark:bg-ink-900 sm:pt-32">
    <div class="container-x max-w-3xl!">
        <nav class="reveal reveal-left mb-6 text-sm text-ink-400" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-maroon-700 dark:hover:text-gold-400">Beranda</a>
            <span aria-hidden="true"> / </span>
            <a href="{{ route('articles.index') }}" class="hover:text-maroon-700 dark:hover:text-gold-400">Artikel</a>
        </nav>

        <header class="reveal">
            @if ($article->published_at)
                <time datetime="{{ $article->published_at->toDateString() }}" class="text-sm font-semibold uppercase tracking-wide text-gold-600">
                    {{ $article->published_at->translatedFormat('d F Y') }}
                </time>
            @endif
            <h1 class="mt-2 font-display text-3xl font-extrabold leading-tight text-ink-900 dark:text-cream-100 sm:text-4xl">{{ $article->title }}</h1>
        </header>

        @if ($article->thumbnail_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($article->thumbnail_path) }}" alt="{{ $article->title }}" class="reveal reveal-scale mt-8 w-full rounded-xl object-cover shadow-sm">
        @endif

        <div class="reveal prose prose-lg mt-8 max-w-none prose-headings:font-display prose-headings:text-ink-800 prose-a:text-maroon-700 hover:prose-a:text-maroon-800 dark:prose-invert dark:prose-a:text-gold-400">
            {!! $article->body !!}
        </div>

        <div class="reveal mt-12 border-t border-ink-100 pt-6 dark:border-ink-800">
            <a href="{{ route('articles.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-maroon-700 hover:text-maroon-800 dark:text-gold-400">
                <span aria-hidden="true">&larr;</span> Kembali ke daftar artikel
            </a>
        </div>
    </div>
</article>
@endsection
