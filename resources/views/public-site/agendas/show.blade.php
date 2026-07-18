@extends('layouts.public')

@section('title', $agenda->title)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags((string) $agenda->description), 160))

@section('content')
<article class="bg-white pb-16 pt-28 dark:bg-ink-900 sm:pt-32">
    <div class="container-x max-w-3xl!">
        <nav class="reveal reveal-left mb-6 text-sm text-ink-400" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-maroon-700 dark:hover:text-gold-400">Beranda</a>
            <span aria-hidden="true"> / </span>
            <a href="{{ route('home') }}#agenda" class="hover:text-maroon-700 dark:hover:text-gold-400">Agenda</a>
        </nav>

        <span class="reveal inline-block rounded-full px-3 py-1 text-xs font-medium {{ $agenda->event_status->badgeClasses() }}">
            {{ $agenda->event_status->label() }}
        </span>

        <h1 class="reveal mt-3 font-display text-3xl font-extrabold leading-tight text-ink-900 dark:text-cream-100 sm:text-4xl" style="--reveal-delay: 70ms">{{ $agenda->title }}</h1>

        @if ($agenda->poster_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($agenda->poster_path) }}" alt="Poster {{ $agenda->title }}" fetchpriority="high" decoding="async" class="reveal reveal-scale mt-8 w-full rounded-xl object-cover shadow-sm">
        @endif

        <dl class="reveal surface mt-8 grid gap-5 p-6 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-gold-600">Waktu Mulai</dt>
                <dd class="mt-1 text-ink-700 dark:text-ink-200">{{ $agenda->starts_at->translatedFormat('l, d F Y · H.i') }} WIB</dd>
            </div>
            @if ($agenda->ends_at)
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gold-600">Waktu Selesai</dt>
                    <dd class="mt-1 text-ink-700 dark:text-ink-200">{{ $agenda->ends_at->translatedFormat('l, d F Y · H.i') }} WIB</dd>
                </div>
            @endif
            @if ($agenda->location)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gold-600">Lokasi</dt>
                    <dd class="mt-1 text-ink-700 dark:text-ink-200">{{ $agenda->location }}</dd>
                </div>
            @endif
        </dl>

        @if ($agenda->description)
            <div class="reveal prose prose-lg mt-8 max-w-none dark:prose-invert">
                {!! nl2br(e($agenda->description)) !!}
            </div>
        @endif

        <div class="reveal mt-12 border-t border-ink-100 pt-6 dark:border-ink-800">
            <a href="{{ route('home') }}#agenda" class="inline-flex items-center gap-1 text-sm font-semibold text-maroon-700 hover:text-maroon-800 dark:text-gold-400">
                <span aria-hidden="true">&larr;</span> Kembali ke agenda
            </a>
        </div>
    </div>
</article>
@endsection
