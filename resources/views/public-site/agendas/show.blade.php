@extends('layouts.public')

@section('title', $agenda->title)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags((string) $agenda->description), 160))
@section('og_type', 'article')
@if ($agenda->poster_path)
    @section('og_image', \Illuminate\Support\Facades\Storage::url($agenda->poster_path))
@endif

@section('head')
    @php
        $agendaImagePath = $agenda->poster_path
            ? \Illuminate\Support\Facades\Storage::url($agenda->poster_path)
            : null;
        $agendaImageUrl = $agendaImagePath
            ? (\Illuminate\Support\Str::startsWith($agendaImagePath, ['http://', 'https://']) ? $agendaImagePath : url($agendaImagePath))
            : null;
        $eventJsonLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            '@id' => route('agendas.show', $agenda).'#event',
            'name' => $agenda->title,
            'description' => $agenda->description ?: null,
            'startDate' => $agenda->starts_at->toIso8601String(),
            'endDate' => optional($agenda->ends_at)->toIso8601String(),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'location' => $agenda->location ? [
                '@type' => 'Place',
                'name' => $agenda->location,
                'address' => $agenda->location,
            ] : null,
            'image' => $agendaImageUrl ? [$agendaImageUrl] : null,
            'url' => route('agendas.show', $agenda),
            'inLanguage' => 'id-ID',
            'organizer' => ['@id' => url('/').'#organization'],
        ], fn ($value) => filled($value));
        $breadcrumbJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Agenda', 'item' => route('home').'#agenda'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $agenda->title, 'item' => route('agendas.show', $agenda)],
            ],
        ];
    @endphp
    <script type="application/ld+json" nonce="{{ request()->attributes->get('csp_nonce') }}">{!! json_encode([$eventJsonLd, $breadcrumbJsonLd], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection

@section('content')
@php($effectiveStatus = $agenda->effectiveEventStatus())
<article class="relative isolate overflow-hidden pb-16 pt-28 text-cream-50 sm:pt-32">
    <x-public-site.synced-background :profile="$profile" />

    <div class="container-x max-w-3xl!">
        <div class="rounded-2xl border border-white/15 bg-maroon-950/60 p-6 shadow-2xl shadow-black/25 backdrop-blur-md sm:p-8">
        <nav class="reveal reveal-left mb-6 text-sm font-medium text-cream-100/85" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-gold-300">Beranda</a>
            <span aria-hidden="true"> / </span>
            <a href="{{ route('home') }}#agenda" class="hover:text-gold-300">Agenda</a>
        </nav>

        <span class="reveal inline-block rounded-full px-3 py-1 text-xs font-medium {{ $effectiveStatus->badgeClasses() }}">
            {{ $effectiveStatus->label() }}
        </span>

        <h1 class="reveal mt-3 font-display text-3xl font-extrabold leading-tight text-cream-50 sm:text-4xl" style="--reveal-delay: 70ms">{{ $agenda->title }}</h1>

        @if ($agenda->poster_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($agenda->poster_path) }}" alt="Poster {{ $agenda->title }}" fetchpriority="high" decoding="async" class="reveal reveal-scale mt-8 w-full rounded-xl object-cover shadow-sm">
        @endif

        <dl class="reveal surface mt-8 grid gap-5 p-6 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-gold-600">Waktu Mulai</dt>
                <dd class="mt-1 text-ink-700">{{ $agenda->starts_at->translatedFormat('l, d F Y · H.i') }} WIB</dd>
            </div>
            @if ($agenda->ends_at)
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gold-600">Waktu Selesai</dt>
                    <dd class="mt-1 text-ink-700">{{ $agenda->ends_at->translatedFormat('l, d F Y · H.i') }} WIB</dd>
                </div>
            @endif
            @if ($agenda->location)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gold-600">Lokasi</dt>
                    <dd class="mt-1 text-ink-700">{{ $agenda->location }}</dd>
                </div>
            @endif
        </dl>

        @if ($agenda->description)
            <div class="public-detail-copy reveal prose prose-invert prose-lg mt-8 max-w-none">
                {!! nl2br(e($agenda->description)) !!}
            </div>
        @endif

        <div class="reveal mt-12 border-t border-white/15 pt-6">
            <a href="{{ route('home') }}#agenda" class="inline-flex items-center gap-1 text-sm font-semibold text-gold-300 hover:text-gold-200">
                <span aria-hidden="true">&larr;</span> Kembali ke agenda
            </a>
        </div>
        </div>
    </div>
</article>
@endsection
