@extends('layouts.public')

@section('content')

{{-- ============================ HERO ============================ --}}
<x-public-site.synced-background :profile="$profile" fixed />

<div class="home-scenes">
<section id="beranda" class="relative isolate overflow-hidden text-cream-50">
    <div class="container-x relative flex min-h-[88svh] flex-col justify-center pb-20 pt-36 sm:pt-40">
        <div class="max-w-3xl">
            <div>
                <p class="reveal reveal-left eyebrow text-gold-400!" style="--reveal-delay: 0ms">
                    <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span>
                    {{ $profile->hero_eyebrow ?: $site->organization_name }}
                </p>

                <h1 class="reveal reveal-left mt-6 font-display text-4xl font-extrabold leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl" style="--reveal-delay: 90ms">
                    {{ $profile->hero_title }}
                </h1>

                @if ($profile->hero_subtitle)
                    <p class="reveal reveal-left mt-6 max-w-xl text-base leading-relaxed text-cream-100/85 sm:text-lg" style="--reveal-delay: 170ms">
                        {{ $profile->hero_subtitle }}
                    </p>
                @endif

                <div class="reveal reveal-left mt-9 flex flex-wrap gap-3" style="--reveal-delay: 250ms">
                    <a href="#tentang" class="btn-gold">{{ $profile->hero_primary_cta_label ?: 'Tentang FPK' }}</a>
                    @if ($publicContentVisibility['agendas'])
                        <a href="#agenda" class="btn-ghost-light">{{ $profile->hero_secondary_cta_label ?: 'Lihat Agenda' }}</a>
                    @endif
                </div>
            </div>

        </div>

        @php($heroFacts = collect([
            [$profile->hero_legal_basis_label ?: 'Dasar Hukum', $profile->institution_legal_basis],
            [$profile->hero_foundation_label ?: 'Landasan', $profile->institution_foundation],
            [$profile->hero_period_label ?: 'Masa Bakti', $activePeriod?->label()],
        ])->filter(fn (array $fact) => filled($fact[1]))->values())
        @if ($heroFacts->isNotEmpty())
            <dl @class([
                'public-facts-card reveal reveal-scale mt-14 grid grid-cols-1 overflow-hidden rounded-xl',
                'sm:grid-cols-2' => $heroFacts->count() === 2,
                'sm:grid-cols-3' => $heroFacts->count() >= 3,
            ]) style="--reveal-delay: 320ms">
                @foreach ($heroFacts as [$label, $value])
                    <div class="public-facts-card__item px-5 py-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider">{{ $label }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-ink-800">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>
</section>

{{-- ============================ TENTANG ============================ --}}
{{-- Offset anchor ditangani terpusat lewat scroll-padding-top pada <html>.
     Section tidak lagi memakai scroll-mt agar kedua nilai tidak saling menumpuk. --}}
<section id="tentang" class="home-scene home-scene--compact">
    <div class="container-x">
        <div class="p-6 sm:p-8 lg:p-12">
        <div class="reveal reveal-scale mx-auto max-w-2xl text-center">
            <span class="eyebrow text-gold-400!">Profil Organisasi</span>
            <h2 class="section-title mt-3 text-cream-50!">Tentang {{ $site->abbreviation ?: $site->site_name }}</h2>
            <span class="title-rule mx-auto"></span>
        </div>

        {{-- Grid ini sengaja identik dengan grid blok profil di bawahnya, dan kartu
             gabungan meng-span 2 kolom, supaya lebarnya persis sama di semua breakpoint. --}}
        <div class="mt-12 grid gap-8 md:grid-cols-2 xl:grid-cols-[repeat(2,33rem)] xl:justify-center">
            <article class="about-feature-card reveal reveal-scale grid min-w-0 overflow-hidden md:col-span-2 lg:grid-cols-2 lg:items-stretch">
                <figure class="about-feature-card__media group relative h-full min-w-0 overflow-hidden">
                    @if ($profile->about_image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($profile->about_image_path) }}"
                         alt="Ilustrasi Tugu Malang dan Balai Kota Malang sebagai identitas {{ $site->organization_name }}"
                         width="1400" height="1050"
                         loading="lazy" decoding="async" fetchpriority="low"
                         class="aspect-4/3 h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.025] lg:aspect-auto">
                    @else
                    <img src="{{ asset('assets/images/about/about-fpk-vector-960.webp') }}"
                         srcset="{{ asset('assets/images/about/about-fpk-vector-480.webp') }} 480w,
                                 {{ asset('assets/images/about/about-fpk-vector-960.webp') }} 960w,
                                 {{ asset('assets/images/about/about-fpk-vector.webp') }} 1400w"
                         sizes="(min-width: 1024px) 50vw, 100vw"
                         alt="Ilustrasi Tugu Malang dan Balai Kota Malang sebagai identitas {{ $site->organization_name }}"
                         width="1400" height="1050"
                         loading="lazy" decoding="async" fetchpriority="low"
                         class="aspect-4/3 h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.025] lg:aspect-auto">
                    @endif
                </figure>

                @if ($profile->definition)
                    <div class="about-feature-card__content flex h-full min-w-0 flex-col justify-center p-7 sm:p-10 lg:p-12">
                        <p class="about-card-kicker">Mengenal FPK</p>
                        <h3 class="about-card-title mt-2">Pengertian</h3>
                        <span class="about-card-rule" aria-hidden="true"></span>
                        <div class="about-card-copy mt-5 text-base sm:text-[1.05rem]">
                            <x-public-site.rich-text :text="$profile->definition" />
                        </div>
                    </div>
                @endif
            </article>
        </div>

        <div class="mt-10 grid gap-8 md:auto-rows-fr md:grid-cols-2 xl:grid-cols-[repeat(2,33rem)] xl:justify-center">
            @php($aboutBlocks = [
                ['background', 'Latar Belakang', 'Konteks Organisasi', false, 'story'],
                ['objectives', 'Tujuan', 'Arah Bersama', true, 'purpose'],
                ['core_tasks', 'Tugas Pokok', 'Mandat Utama', true, 'mission'],
                ['legal_basis', 'Dasar Hukum', 'Landasan Resmi', true, 'legal'],
            ])
            @foreach ($aboutBlocks as [$field, $label, $kicker, $asList, $tone])
                @if ($profile->{$field})
                    <article class="about-info-card about-info-card--{{ $tone }} reveal {{ $loop->odd ? 'reveal-left' : 'reveal-right' }} flex min-w-0 w-full flex-col p-7 sm:p-8" style="--reveal-delay: {{ ($loop->index % 2) * 70 }}ms">
                        <div class="relative z-10 min-w-0">
                            <p class="about-card-kicker">{{ $kicker }}</p>
                            <h3 class="about-card-title mt-2">{{ $label }}</h3>
                        </div>
                        <span class="about-card-rule relative z-10" aria-hidden="true"></span>
                        <div class="about-card-copy relative z-10 mt-5 flex-1">
                            @if ($asList)
                                <x-public-site.rich-list :text="$profile->{$field}" class="space-y-3.5!" />
                            @else
                                <x-public-site.rich-text :text="$profile->{$field}" />
                            @endif
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
        </div>
    </div>
</section>

{{-- ============================ AGENDA ============================ --}}
@if ($upcomingAgendas->isNotEmpty())
<section id="agenda" class="home-scene">
    <div class="container-x">
        <div class="mx-auto max-w-4xl p-6 sm:p-8 lg:p-12">
        <div class="reveal reveal-scale text-center">
            <span class="eyebrow text-gold-400!">Jadwal Kegiatan</span>
            <h2 class="section-title mt-3 text-cream-50!">Agenda Kegiatan</h2>
            <span class="title-rule mx-auto"></span>
        </div>

        <div class="reveal mt-12">
            <h3 class="font-display text-xl font-bold text-cream-50">Mendatang &amp; Berlangsung</h3>
            <span class="title-rule"></span>
        </div>
        <div class="mt-6 space-y-4">
            @foreach ($upcomingAgendas as $agenda)
                <div class="reveal" style="--reveal-delay: {{ $loop->index * 70 }}ms">
                    <x-public-site.agenda-card :agenda="$agenda" />
                </div>
            @endforeach
        </div>

        </div>
    </div>
</section>
@endif

{{-- ============================ GALERI ============================ --}}
@if ($galleryImages->isNotEmpty())
<section id="galeri" class="home-scene home-scene--compact" data-public-gallery>
    <div class="container-x">
        <div class="p-6 sm:p-8 lg:p-12">
            <div class="reveal reveal-scale text-center">
                <span class="eyebrow text-gold-400!">Dokumentasi Kegiatan</span>
                <h2 class="section-title mt-3 text-cream-50!">Galeri</h2>
                <span class="title-rule mx-auto"></span>
            </div>

            <div @class([
                'mt-10 grid gap-3 sm:gap-4',
                'mx-auto max-w-3xl grid-cols-1' => $galleryImages->count() === 1,
                'sm:grid-cols-2' => $galleryImages->count() === 2,
                'grid-cols-2 lg:grid-cols-3' => $galleryImages->count() >= 3,
            ])>
                @foreach ($galleryImages as $galleryImage)
                    <figure
                        class="reveal reveal-scale group overflow-hidden rounded-2xl border border-white/20 bg-maroon-950/30 shadow-xl shadow-black/15"
                        style="--reveal-delay: {{ ($loop->index % 6) * 55 }}ms"
                        data-public-gallery-item
                    >
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::url($galleryImage->image_path) }}"
                            alt="Dokumentasi kegiatan {{ $site->abbreviation ?: $site->site_name }} foto ke-{{ $loop->iteration }}"
                            width="800"
                            height="600"
                            loading="lazy"
                            decoding="async"
                            fetchpriority="low"
                            class="aspect-4/3 h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.025]"
                        >
                    </figure>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- ============================ ARTIKEL ============================ --}}
@if ($featuredArticle)
<section id="artikel" class="home-scene home-scene--compact">
    <div class="container-x">
        <div class="p-6 sm:p-8 lg:p-12">
        <div class="reveal reveal-left flex flex-wrap items-end justify-between gap-4">
            <div>
                <span class="eyebrow text-gold-400!">Kabar Terbaru</span>
                <h2 class="section-title mt-3 text-cream-50!">Artikel Terbaru</h2>
                <span class="title-rule"></span>
            </div>
            <a href="{{ route('articles.index') }}" class="text-sm font-semibold text-cream-100 hover:text-gold-300">Lihat semua artikel &rarr;</a>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-2 lg:items-stretch">
                {{-- Featured article: dominant editorial card. --}}
                <article class="reveal reveal-left group surface card-lift flex flex-col overflow-hidden">
                    <a href="{{ route('articles.show', $featuredArticle) }}" class="relative block aspect-16/10 overflow-hidden bg-cream-100">
                        @if ($featuredArticle->thumbnail_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($featuredArticle->thumbnail_path) }}" alt="{{ $featuredArticle->title }}" loading="lazy" decoding="async" fetchpriority="low" class="h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105">
                        @else
                            <span class="flex h-full w-full items-center justify-center font-display text-5xl text-maroon-200" aria-hidden="true">FPK</span>
                        @endif
                        <span class="absolute left-4 top-4 inline-flex items-center gap-1 rounded-full bg-gold-500 px-3 py-1 text-xs font-semibold text-maroon-950 shadow-sm">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.8 6.2 20.9l1.1-6.5L2.6 9.8l6.5-.9z"/></svg>
                            Unggulan
                        </span>
                    </a>
                    <div class="flex flex-1 flex-col p-6">
                        @if ($featuredArticle->published_at)
                            <time datetime="{{ $featuredArticle->published_at->toDateString() }}" class="text-xs font-semibold uppercase tracking-wide text-gold-600">
                                {{ $featuredArticle->published_at->translatedFormat('d F Y') }}
                            </time>
                        @endif
                        <h3 class="mt-2 font-display text-2xl font-bold leading-snug text-ink-800">
                            <a href="{{ route('articles.show', $featuredArticle) }}" class="transition hover:text-maroon-700">{{ $featuredArticle->title }}</a>
                        </h3>
                        @if ($featuredArticle->excerpt)
                            <p class="mt-3 line-clamp-3 text-sm font-medium leading-relaxed text-ink-700">{{ $featuredArticle->excerpt }}</p>
                        @endif
                        <a href="{{ route('articles.show', $featuredArticle) }}" class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-maroon-700 transition group-hover:gap-2">
                            Baca selengkapnya <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </article>

                {{-- Secondary articles: compact horizontal rows. --}}
                @if ($latestArticles->isNotEmpty())
                    <div class="flex flex-col gap-4">
                        @foreach ($latestArticles as $article)
                            <article class="reveal reveal-right group surface card-lift flex gap-4 overflow-hidden p-4" style="--reveal-delay: {{ $loop->index * 80 }}ms">
                                <a href="{{ route('articles.show', $article) }}" class="block aspect-square w-24 flex-none overflow-hidden rounded-lg bg-cream-100 sm:w-28">
                                    @if ($article->thumbnail_path)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($article->thumbnail_path) }}" alt="{{ $article->title }}" loading="lazy" decoding="async" fetchpriority="low" class="h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105">
                                    @else
                                        <span class="flex h-full w-full items-center justify-center font-display text-lg text-maroon-200" aria-hidden="true">FPK</span>
                                    @endif
                                </a>
                                <div class="min-w-0 flex-1">
                                    @if ($article->published_at)
                                        <time datetime="{{ $article->published_at->toDateString() }}" class="text-xs font-semibold uppercase tracking-wide text-gold-600">
                                            {{ $article->published_at->translatedFormat('d F Y') }}
                                        </time>
                                    @endif
                                    <h3 class="mt-1 font-display text-base font-bold leading-snug text-ink-800">
                                        <a href="{{ route('articles.show', $article) }}" class="transition hover:text-maroon-700">{{ $article->title }}</a>
                                    </h3>
                                    @if ($article->excerpt)
                                        <p class="mt-1 line-clamp-2 text-sm font-medium text-ink-700">{{ $article->excerpt }}</p>
                                    @endif
                                    <a href="{{ route('articles.show', $article) }}" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-maroon-700 transition hover:text-maroon-800">
                                        Baca artikel <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
        </div>
        </div>
    </div>
</section>
@endif

{{-- ============================ PENGURUS ============================ --}}
@if ($activePeriod && ($activePeriod->group_photo_path || $activePeriod->activeMembers->isNotEmpty()))
<section id="pengurus" class="home-scene">
    <div class="container-x">
        <div class="p-6 sm:p-8 lg:p-12">
        <div class="reveal reveal-scale text-center">
            <span class="eyebrow text-gold-400!">Struktur Organisasi</span>
            <h2 class="section-title mt-3 text-cream-50!">Susunan Pengurus</h2>
            <span class="title-rule mx-auto"></span>
            @if ($activePeriod)
                <p class="mt-3 text-cream-100/80">Masa Bakti {{ $activePeriod->label() }}</p>
            @endif
        </div>

        @if ($activePeriod->group_photo_path)
            <figure class="reveal reveal-scale group relative mt-12 overflow-hidden rounded-2xl border border-maroon-100 bg-maroon-950 shadow-xl shadow-maroon-950/15">
                <div class="aspect-16/7 min-h-64 sm:min-h-80">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($activePeriod->group_photo_path) }}"
                         alt="Foto bersama pengurus {{ $site->organization_name }} masa bakti {{ $activePeriod->label() }}"
                         width="1400" height="613"
                         loading="lazy" decoding="async" fetchpriority="low"
                         class="h-full w-full object-cover transition duration-1000 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.015]">
                </div>
                <figcaption class="absolute inset-x-0 bottom-0 bg-linear-to-t from-maroon-950/90 via-maroon-950/55 to-transparent px-5 pb-5 pt-16 text-cream-50 sm:px-7 sm:pb-7">
                    <p class="font-display text-xl font-bold sm:text-2xl">Kebersamaan Pengurus {{ $site->abbreviation ?: $site->site_name }}</p>
                    <p class="mt-1 text-sm text-cream-100/75">Masa Bakti {{ $activePeriod->label() }}</p>
                </figcaption>
            </figure>
        @endif

        @if ($activePeriod->activeMembers->isNotEmpty())
            <div x-data="memberCarousel" data-member-carousel
                 class="reveal reveal-scale mt-10"
                 x-on:resize.window.debounce.150ms="sync()"
                 x-on:keydown.arrow-left.prevent="move(-1)"
                 x-on:keydown.arrow-right.prevent="move(1)">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow text-gold-400!">Profil Pengurus</p>
                        <h3 class="mt-2 font-display text-2xl font-bold text-cream-50">Kenali Pengurus Kami</h3>
                        <p class="mt-1 text-sm text-cream-100/80">Geser kartu ke samping untuk melihat seluruh pengurus.</p>
                    </div>

                    <div class="hidden gap-2 sm:flex" aria-label="Kontrol carousel pengurus">
                        <button type="button" x-on:click="move(-1)" :disabled="!canPrevious"
                                class="icon-button grid h-11 w-11 place-items-center rounded-full border border-maroon-200 bg-white text-maroon-700 shadow-sm disabled:cursor-not-allowed disabled:opacity-35"
                                aria-label="Pengurus sebelumnya">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" x-on:click="move(1)" :disabled="!canNext"
                                class="icon-button grid h-11 w-11 place-items-center rounded-full border border-maroon-200 bg-white text-maroon-700 shadow-sm disabled:cursor-not-allowed disabled:opacity-35"
                                aria-label="Pengurus berikutnya">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <div x-ref="track" x-on:scroll.debounce.80ms="sync()" tabindex="0"
                     class="member-carousel-track -mx-6 flex items-stretch gap-5 overflow-x-auto px-6 pb-5 sm:-mx-8 sm:gap-6 sm:px-8 lg:mx-0 lg:px-0"
                     aria-label="Daftar kartu pengurus">
                    @foreach ($activePeriod->activeMembers as $member)
                        <x-public-site.member-card :member="$member"
                            data-member-card
                            class="h-128 w-full flex-none snap-start sm:w-64 lg:w-[calc((100%-3rem)/3)] lg:max-w-none" />
                    @endforeach
                </div>
            </div>
        @endif
        </div>
    </div>
</section>
@endif

{{-- ============================ KONTAK ============================ --}}
@if ($publicContentVisibility['contact'])
<section id="kontak" class="home-scene text-cream-50">
    <div class="container-x">
        <div class="home-scene-panel home-scene-panel--dark p-6 sm:p-8 lg:p-12">
        <div class="reveal reveal-scale text-center">
            <span class="eyebrow text-gold-400!">Hubungi Kami</span>
            <h2 class="section-title mt-3 text-cream-100!">Kontak &amp; Media Sosial</h2>
            <span class="title-rule mx-auto"></span>
        </div>

        <div @class([
            'mt-12 grid gap-8',
            'md:grid-cols-2' => $contact->hasAnyContact() && $contact->map_embed_url,
            'mx-auto max-w-3xl' => ! ($contact->hasAnyContact() && $contact->map_embed_url),
        ])>
            @if ($contact->hasAnyContact())
                <div class="reveal reveal-left space-y-6 text-sm">
                    @if ($contact->address)
                        <div><p class="font-semibold text-gold-400">Alamat</p><p class="mt-1 text-cream-100/85">{{ $contact->address }}</p></div>
                    @endif
                    <div class="grid gap-5 sm:grid-cols-2">
                        @if ($contact->phone)<div><p class="font-semibold text-gold-400">Telepon</p><p class="mt-1 text-cream-100/85">{{ $contact->phone }}</p></div>@endif
                        @if ($contact->whatsappLink())<div><p class="font-semibold text-gold-400">WhatsApp</p><a href="{{ $contact->whatsappLink() }}" target="_blank" rel="noopener noreferrer" class="mt-1 block text-cream-100/85 hover:text-white">{{ $contact->whatsapp }}</a></div>@endif
                        @if ($contact->email)<div><p class="font-semibold text-gold-400">Email</p><a href="mailto:{{ $contact->email }}" class="mt-1 block text-cream-100/85 hover:text-white">{{ $contact->email }}</a></div>@endif
                        @if ($contact->operational_hours)<div><p class="font-semibold text-gold-400">Jam Operasional</p><p class="mt-1 text-cream-100/85">{{ $contact->operational_hours }}</p></div>@endif
                    </div>
                    @if ($contact->instagram_url || $contact->facebook_url || $contact->youtube_url || $contact->tiktok_url)
                        <div>
                            <p class="font-semibold text-gold-400">Media Sosial</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach (['instagram_url' => 'Instagram', 'facebook_url' => 'Facebook', 'youtube_url' => 'YouTube', 'tiktok_url' => 'TikTok'] as $field => $label)
                                    @if ($contact->{$field})<a href="{{ $contact->{$field} }}" target="_blank" rel="noopener noreferrer" class="rounded-md border border-cream-100/25 px-3 py-1 transition hover:border-gold-400 hover:text-white">{{ $label }}</a>@endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if ($contact->map_embed_url)
                <div class="reveal reveal-right overflow-hidden rounded-xl border border-cream-100/15">
                    <iframe src="{{ $contact->map_embed_url }}" title="Peta lokasi {{ $site->organization_name }}" class="h-72 w-full md:h-full" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" sandbox="allow-scripts allow-same-origin allow-popups allow-forms" allowfullscreen></iframe>
                </div>
            @endif
        </div>
        </div>
    </div>
</section>
@endif
</div>

@endsection
