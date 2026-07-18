@extends('layouts.public')

@section('content')

{{-- ============================ HERO ============================ --}}
<section id="beranda" class="relative isolate overflow-hidden bg-maroon-950 text-cream-50">
    <div class="hero-motif parallax-layer pointer-events-none absolute inset-0 -z-10" data-parallax="0.012" aria-hidden="true"></div>
    <div class="hero-glow parallax-layer pointer-events-none absolute inset-0 -z-10" data-parallax="0.032" aria-hidden="true"></div>
    <div class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-40 bg-linear-to-t from-maroon-950 to-transparent" aria-hidden="true"></div>

    <div class="container-x relative flex min-h-[88vh] flex-col justify-center pb-20 pt-36 sm:pt-40">
        <div class="grid items-center gap-12 lg:grid-cols-[1.05fr_0.95fr]">
            {{-- Left: message --}}
            <div>
                <p class="reveal reveal-left eyebrow text-gold-400!" style="--reveal-delay: 0ms">
                    <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span>
                    Forum Pembauran Kebangsaan &middot; Kota Malang
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
                    <a href="#tentang" class="btn-gold">Tentang FPK</a>
                    @if ($publicContentVisibility['agendas'])
                        <a href="{{ $upcomingAgendas->isNotEmpty() ? '#agenda' : route('agendas.index') }}" class="btn-ghost-light">Lihat Agenda</a>
                    @endif
                </div>
            </div>

            {{-- Right: featured visual, with a composed fallback when no image is set --}}
            <div class="reveal reveal-right reveal-scale relative" style="--reveal-delay: 200ms">
                <div class="hero-visual parallax-layer relative aspect-4/5 overflow-hidden rounded-2xl border border-cream-100/15 bg-maroon-900/40 shadow-2xl shadow-black/30 sm:aspect-square lg:aspect-4/5" data-parallax="0.025">
                    @if ($profile->hero_image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($profile->hero_image_path) }}"
                             alt="Kegiatan Forum Pembauran Kebangsaan Kota Malang"
                             width="640" height="800"
                             fetchpriority="high" decoding="async"
                             class="h-full w-full object-cover">
                        <div class="pointer-events-none absolute inset-0 bg-linear-to-t from-maroon-950/70 via-transparent to-transparent" aria-hidden="true"></div>
                    @else
                        <img src="{{ asset('assets/images/branding/hero-card-bg.webp') }}"
                             alt=""
                             width="960" height="1200"
                             fetchpriority="high" decoding="async"
                             class="absolute inset-0 h-full w-full object-cover"
                             aria-hidden="true">
                        <div class="absolute inset-0 bg-black/10" aria-hidden="true"></div>
                        <div class="relative z-10 flex h-full flex-col items-center justify-center px-6 text-center sm:px-8">
                            <img src="{{ asset('assets/images/branding/logo-fpk.png') }}"
                                 alt="Logo FPK Kota Malang"
                                 width="144" height="144"
                                 decoding="async"
                                 class="float-slow h-24 w-24 rounded-full bg-white p-3 shadow-2xl ring-1 ring-gold-400/35 sm:h-28 sm:w-28 md:h-36 md:w-36 md:p-4">
                            <p class="mt-6 font-display text-xl font-bold text-cream-50 sm:text-2xl">FPK Kota Malang</p>
                            <p class="mt-2 max-w-sm text-sm leading-relaxed text-cream-100/75">Merawat kebhinnekaan, memperkuat persatuan.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Dasar hukum sebagai penanda kredibilitas (faktual dari SK). --}}
        <dl class="reveal reveal-scale mt-14 grid grid-cols-1 gap-px overflow-hidden rounded-xl border border-cream-100/15 bg-cream-100/5 sm:grid-cols-3" style="--reveal-delay: 320ms">
            @foreach ([
                ['Dasar Hukum', 'Pergub Jatim No. 41/2009'],
                ['Landasan', 'SK Wali Kota Malang'],
                ['Masa Bakti', '2025 – 2027'],
            ] as [$label, $value])
                <div class="bg-maroon-950/40 px-5 py-4">
                    <dt class="text-xs uppercase tracking-wider text-gold-400/90">{{ $label }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-cream-50">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>

{{-- ============================ TENTANG ============================ --}}
<section id="tentang" class="section scroll-mt-24 bg-cream-50 dark:bg-ink-950">
    <div class="container-x">
        <div class="reveal reveal-scale mx-auto max-w-2xl text-center">
            <span class="eyebrow">Profil Organisasi</span>
            <h2 class="section-title mt-3">Tentang FPK Kota Malang</h2>
            <span class="title-rule mx-auto"></span>
        </div>

        <div class="mt-12 grid gap-8 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
            <div class="reveal reveal-left surface p-6 sm:p-8">
                <div class="text-base leading-relaxed text-ink-600 dark:text-ink-300 sm:text-lg">
                    @if ($profile->definition)
                        <x-public-site.rich-text :text="$profile->definition" />
                    @else
                        <p>
                            Forum Pembauran Kebangsaan Kota Malang merupakan wadah informasi, komunikasi,
                            konsultasi, dan kerja sama antarwarga masyarakat. FPK diarahkan untuk menumbuhkan,
                            memantapkan, memelihara, dan mengembangkan pembauran kebangsaan di tengah
                            kemajemukan masyarakat Kota Malang.
                        </p>
                    @endif
                </div>

                <ul class="mt-6 space-y-3">
                    @foreach ([
                        'Menumbuhkan toleransi dan saling menghormati.',
                        'Meningkatkan integrasi dan persatuan masyarakat.',
                        'Mencegah konflik sosial dan disintegrasi.',
                        'Membangun solidaritas dalam bingkai NKRI.',
                    ] as $point)
                        <li class="flex gap-3 text-sm leading-relaxed text-ink-600 dark:text-ink-300">
                            <span class="mt-0.5 grid h-6 w-6 flex-none place-items-center rounded-full bg-maroon-50 text-maroon-700 ring-1 ring-maroon-100 dark:bg-ink-800 dark:text-gold-400 dark:ring-white/10" aria-hidden="true">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <figure class="reveal reveal-right reveal-scale overflow-hidden rounded-2xl border border-cream-200 bg-white shadow-xl shadow-maroon-950/10 dark:border-white/10 dark:bg-ink-900 dark:shadow-black/20" style="--reveal-delay: 100ms">
                <img src="{{ asset('assets/images/about/about-fpk-vector.webp') }}"
                     alt="Ilustrasi Tugu Malang dan Balai Kota Malang sebagai identitas FPK Kota Malang"
                     width="1400" height="1050"
                     loading="lazy" decoding="async"
                     class="aspect-4/3 h-full w-full object-cover transition duration-700 hover:scale-[1.025]">
            </figure>
        </div>

        <div class="mt-10 grid gap-6 md:grid-cols-2">
            @php($aboutBlocks = [
                ['background', 'Latar Belakang', 'M12 3v18m9-9H3', false],
                ['objectives', 'Tujuan', 'M5 13l4 4L19 7', true],
                ['core_tasks', 'Tugas Pokok', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', true],
                ['legal_basis', 'Dasar Hukum', 'M3 6l9-4 9 4M4 10v10h16V10M9 21v-6h6v6', true],
            ])
            @foreach ($aboutBlocks as [$field, $label, $icon, $asList])
                @if ($profile->{$field})
                    <article class="reveal {{ $loop->odd ? 'reveal-left' : 'reveal-right' }} surface card-lift p-6" style="--reveal-delay: {{ ($loop->index % 2) * 70 }}ms">
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 flex-none place-items-center rounded-lg bg-maroon-50 text-maroon-700 dark:bg-ink-800 dark:text-gold-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            </span>
                            <h3 class="font-display text-lg font-bold text-maroon-800 dark:text-cream-100">{{ $label }}</h3>
                        </div>
                        <div class="mt-4 text-ink-600 dark:text-ink-300">
                            @if ($asList)
                                <x-public-site.rich-list :text="$profile->{$field}" />
                            @else
                                <x-public-site.rich-text :text="$profile->{$field}" />
                            @endif
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</section>

{{-- ============================ ARTIKEL ============================ --}}
@if ($featuredArticle)
<section id="artikel" class="section scroll-mt-24 bg-white dark:bg-ink-900">
    <div class="container-x">
        <div class="reveal reveal-left flex flex-wrap items-end justify-between gap-4">
            <div>
                <span class="eyebrow">Kabar Terbaru</span>
                <h2 class="section-title mt-3">Artikel Terbaru</h2>
                <span class="title-rule"></span>
            </div>
            <a href="{{ route('articles.index') }}" class="text-sm font-semibold text-maroon-700 hover:text-maroon-800 dark:text-gold-400">Lihat semua artikel &rarr;</a>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-2 lg:items-stretch">
                {{-- Featured article: dominant editorial card. --}}
                <article class="reveal reveal-left group surface card-lift flex flex-col overflow-hidden">
                    <a href="{{ route('articles.show', $featuredArticle) }}" class="relative block aspect-16/10 overflow-hidden bg-cream-100 dark:bg-ink-800">
                        @if ($featuredArticle->thumbnail_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($featuredArticle->thumbnail_path) }}" alt="{{ $featuredArticle->title }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <span class="flex h-full w-full items-center justify-center font-display text-5xl text-maroon-200 dark:text-ink-600" aria-hidden="true">FPK</span>
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
                        <h3 class="mt-2 font-display text-2xl font-bold leading-snug text-ink-800 dark:text-cream-100">
                            <a href="{{ route('articles.show', $featuredArticle) }}" class="transition hover:text-maroon-700 dark:hover:text-gold-400">{{ $featuredArticle->title }}</a>
                        </h3>
                        @if ($featuredArticle->excerpt)
                            <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-ink-500 dark:text-ink-400">{{ $featuredArticle->excerpt }}</p>
                        @endif
                        <a href="{{ route('articles.show', $featuredArticle) }}" class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-maroon-700 transition group-hover:gap-2 dark:text-gold-400">
                            Baca selengkapnya <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </article>

                {{-- Secondary articles: compact horizontal rows. --}}
                @if ($latestArticles->isNotEmpty())
                    <div class="flex flex-col divide-y divide-cream-200 dark:divide-ink-800">
                        @foreach ($latestArticles as $article)
                            <article class="reveal reveal-right group flex gap-4 py-4 first:pt-0 last:pb-0" style="--reveal-delay: {{ $loop->index * 80 }}ms">
                                <a href="{{ route('articles.show', $article) }}" class="block aspect-square w-24 flex-none overflow-hidden rounded-lg bg-cream-100 dark:bg-ink-800 sm:w-28">
                                    @if ($article->thumbnail_path)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($article->thumbnail_path) }}" alt="{{ $article->title }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                    @else
                                        <span class="flex h-full w-full items-center justify-center font-display text-lg text-maroon-200 dark:text-ink-600" aria-hidden="true">FPK</span>
                                    @endif
                                </a>
                                <div class="min-w-0 flex-1">
                                    @if ($article->published_at)
                                        <time datetime="{{ $article->published_at->toDateString() }}" class="text-xs font-semibold uppercase tracking-wide text-gold-600">
                                            {{ $article->published_at->translatedFormat('d F Y') }}
                                        </time>
                                    @endif
                                    <h3 class="mt-1 font-display text-base font-bold leading-snug text-ink-800 dark:text-cream-100">
                                        <a href="{{ route('articles.show', $article) }}" class="transition hover:text-maroon-700 dark:hover:text-gold-400">{{ $article->title }}</a>
                                    </h3>
                                    @if ($article->excerpt)
                                        <p class="mt-1 line-clamp-2 text-sm text-ink-500 dark:text-ink-400">{{ $article->excerpt }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
        </div>
    </div>
</section>
@endif

{{-- ============================ AGENDA ============================ --}}
@if ($upcomingAgendas->isNotEmpty())
<section id="agenda" class="section scroll-mt-24 bg-cream-50 dark:bg-ink-950">
    <div class="container-x max-w-4xl!">
        <div class="reveal reveal-scale text-center">
            <span class="eyebrow">Jadwal Kegiatan</span>
            <h2 class="section-title mt-3">Agenda Mendatang</h2>
            <span class="title-rule mx-auto"></span>
        </div>

        <div class="mt-10 space-y-4">
            @foreach ($upcomingAgendas as $agenda)
                <div class="reveal" style="--reveal-delay: {{ $loop->index * 70 }}ms">
                    <x-public-site.agenda-card :agenda="$agenda" />
                </div>
            @endforeach
        </div>
        <div class="reveal mt-8 text-center">
            <a href="{{ route('agendas.index') }}" class="btn-outline">Lihat Semua Agenda</a>
        </div>
    </div>
</section>
@endif

{{-- ============================ PENGURUS ============================ --}}
@if ($activePeriod && ($activePeriod->group_photo_path || $activePeriod->activeMembers->isNotEmpty()))
<section id="pengurus" class="section scroll-mt-24 bg-white dark:bg-ink-900">
    <div class="container-x">
        <div class="reveal reveal-scale text-center">
            <span class="eyebrow">Struktur Organisasi</span>
            <h2 class="section-title mt-3">Susunan Pengurus</h2>
            <span class="title-rule mx-auto"></span>
            @if ($activePeriod)
                <p class="mt-3 text-ink-500 dark:text-ink-400">Masa Bakti {{ $activePeriod->label() }}</p>
            @endif
        </div>

        @if ($activePeriod->group_photo_path)
                <figure class="reveal reveal-scale group relative mt-12 overflow-hidden rounded-2xl border border-maroon-100 bg-maroon-950 shadow-xl shadow-maroon-950/15 dark:border-white/10 dark:shadow-black/30">
                    <div class="aspect-16/7 min-h-64 sm:min-h-80">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($activePeriod->group_photo_path) }}"
                             alt="Foto bersama pengurus FPK Kota Malang masa bakti {{ $activePeriod->label() }}"
                             width="1400" height="613"
                             loading="lazy" decoding="async"
                             class="h-full w-full object-cover transition duration-1000 group-hover:scale-[1.015]">
                    </div>
                    <figcaption class="absolute inset-x-0 bottom-0 bg-linear-to-t from-maroon-950/90 via-maroon-950/55 to-transparent px-5 pb-5 pt-16 text-cream-50 sm:px-7 sm:pb-7">
                        <p class="font-display text-xl font-bold sm:text-2xl">Kebersamaan Pengurus FPK Kota Malang</p>
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
                            <p class="eyebrow">Profil Pengurus</p>
                            <h3 class="mt-2 font-display text-2xl font-bold text-maroon-800 dark:text-cream-100">Kenali Pengurus Kami</h3>
                            <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Geser kartu ke samping untuk melihat seluruh pengurus.</p>
                        </div>

                        <div class="hidden gap-2 sm:flex" aria-label="Kontrol carousel pengurus">
                            <button type="button" x-on:click="move(-1)" :disabled="!canPrevious"
                                    class="icon-button grid h-11 w-11 place-items-center rounded-full border border-maroon-200 bg-white text-maroon-700 shadow-sm disabled:cursor-not-allowed disabled:opacity-35 dark:border-ink-700 dark:bg-ink-900 dark:text-gold-400"
                                    aria-label="Pengurus sebelumnya">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" x-on:click="move(1)" :disabled="!canNext"
                                    class="icon-button grid h-11 w-11 place-items-center rounded-full border border-maroon-200 bg-white text-maroon-700 shadow-sm disabled:cursor-not-allowed disabled:opacity-35 dark:border-ink-700 dark:bg-ink-900 dark:text-gold-400"
                                    aria-label="Pengurus berikutnya">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>

                    <div x-ref="track" x-on:scroll.debounce.80ms="sync()" tabindex="0"
                         class="member-carousel-track -mx-4 flex gap-5 overflow-x-auto px-4 pb-5 sm:-mx-6 sm:gap-6 sm:px-6 lg:-mx-8 lg:px-8"
                         aria-label="Daftar kartu pengurus">
                        @foreach ($activePeriod->activeMembers as $member)
                            <x-public-site.member-card :member="$member"
                                data-member-card
                                class="w-[78vw] max-w-70 flex-none snap-start sm:w-64 lg:w-68" />
                        @endforeach
                    </div>
                </div>
        @endif
    </div>
</section>
@endif

{{-- ============================ KONTAK ============================ --}}
@if ($publicContentVisibility['contact'])
<section id="kontak" class="section relative isolate scroll-mt-24 overflow-hidden bg-maroon-950 text-cream-50">
    <div class="hero-motif parallax-layer pointer-events-none absolute inset-0 -z-10 opacity-40" data-parallax="0.018" aria-hidden="true"></div>
    <div class="container-x">
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
                    <iframe src="{{ $contact->map_embed_url }}" title="Peta lokasi FPK Kota Malang" class="h-72 w-full md:h-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                </div>
            @endif
        </div>
    </div>
</section>
@endif

@endsection
