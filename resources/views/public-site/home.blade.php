@extends('layouts.public')

@section('content')

{{-- ============================ HERO ============================ --}}
<section id="beranda" class="relative isolate overflow-hidden bg-maroon-950 text-cream-50">
    <div class="hero-motif pointer-events-none absolute inset-0 -z-10" aria-hidden="true"></div>
    <div class="hero-glow pointer-events-none absolute inset-0 -z-10" aria-hidden="true"></div>
    <div class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-40 bg-linear-to-t from-maroon-950 to-transparent" aria-hidden="true"></div>

    <div class="container-x relative flex min-h-[88vh] flex-col justify-center pb-20 pt-36 sm:pt-40">
        <div class="grid items-center gap-12 lg:grid-cols-[1.05fr_0.95fr]">
            {{-- Left: message --}}
            <div>
                <p class="reveal eyebrow text-gold-400!" style="--reveal-delay: 0ms">
                    <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span>
                    Forum Pembauran Kebangsaan &middot; Kota Malang
                </p>

                <h1 class="reveal mt-6 font-display text-4xl font-extrabold leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl" style="--reveal-delay: 90ms">
                    {{ $profile->hero_title }}
                </h1>

                @if ($profile->hero_subtitle)
                    <p class="reveal mt-6 max-w-xl text-base leading-relaxed text-cream-100/85 sm:text-lg" style="--reveal-delay: 170ms">
                        {{ $profile->hero_subtitle }}
                    </p>
                @endif

                <div class="reveal mt-9 flex flex-wrap gap-3" style="--reveal-delay: 250ms">
                    <a href="#tentang" class="btn-gold">Tentang FPK</a>
                    <a href="#agenda" class="btn-ghost-light">Lihat Agenda</a>
                </div>
            </div>

            {{-- Right: featured visual, with a composed fallback when no image is set --}}
            <div class="reveal relative" style="--reveal-delay: 200ms">
                <div class="relative aspect-4/5 overflow-hidden rounded-2xl border border-cream-100/15 bg-maroon-900/40 shadow-2xl shadow-black/30 sm:aspect-square lg:aspect-4/5">
                    @if ($profile->hero_image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($profile->hero_image_path) }}"
                             alt="Kegiatan Forum Pembauran Kebangsaan Kota Malang"
                             width="640" height="800"
                             class="h-full w-full object-cover">
                        <div class="pointer-events-none absolute inset-0 bg-linear-to-t from-maroon-950/70 via-transparent to-transparent" aria-hidden="true"></div>
                    @else
                        <div class="hero-motif absolute inset-0 opacity-50" aria-hidden="true"></div>
                        <div class="absolute inset-0 grid place-items-center p-8 text-center">
                            <div>
                                <span class="mx-auto grid h-24 w-24 place-items-center rounded-full border border-gold-400/40 bg-cream-50/95 font-display text-4xl font-bold text-maroon-800" aria-hidden="true">F</span>
                                <p class="mt-5 font-display text-lg font-semibold text-cream-50">FPK Kota Malang</p>
                                <p class="mt-1 text-sm text-cream-100/70">Merawat kebhinnekaan, memperkuat persatuan.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Dasar hukum sebagai penanda kredibilitas (faktual dari SK). --}}
        <dl class="reveal mt-14 grid grid-cols-1 gap-px overflow-hidden rounded-xl border border-cream-100/15 bg-cream-100/5 sm:grid-cols-3" style="--reveal-delay: 320ms">
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
        <div class="reveal mx-auto max-w-2xl text-center">
            <span class="eyebrow">Profil Organisasi</span>
            <h2 class="section-title mt-3">Tentang FPK Kota Malang</h2>
            <span class="title-rule mx-auto"></span>
        </div>

        @if ($profile->definition)
            <div class="reveal mx-auto mt-8 max-w-3xl text-center text-lg leading-relaxed text-ink-600 dark:text-ink-300">
                <x-public-site.rich-text :text="$profile->definition" />
            </div>
        @endif

        <div class="mt-12 grid gap-6 md:grid-cols-2">
            @php($aboutBlocks = [
                ['background', 'Latar Belakang', 'M12 3v18m9-9H3', false],
                ['objectives', 'Tujuan', 'M5 13l4 4L19 7', true],
                ['core_tasks', 'Tugas Pokok', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', true],
                ['legal_basis', 'Dasar Hukum', 'M3 6l9-4 9 4M4 10v10h16V10M9 21v-6h6v6', true],
            ])
            @foreach ($aboutBlocks as [$field, $label, $icon, $asList])
                @if ($profile->{$field})
                    <article class="reveal surface card-lift p-6">
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
<section id="artikel" class="section scroll-mt-24 bg-white dark:bg-ink-900">
    <div class="container-x">
        <div class="reveal flex flex-wrap items-end justify-between gap-4">
            <div>
                <span class="eyebrow">Kabar Terbaru</span>
                <h2 class="section-title mt-3">Artikel Terbaru</h2>
                <span class="title-rule"></span>
            </div>
            <a href="{{ route('articles.index') }}" class="text-sm font-semibold text-maroon-700 hover:text-maroon-800 dark:text-gold-400">Lihat semua artikel &rarr;</a>
        </div>

        @if ($featuredArticle)
            <div class="mt-10 grid gap-6 lg:grid-cols-2 lg:items-stretch">
                {{-- Featured article: dominant editorial card. --}}
                <article class="reveal group surface card-lift flex flex-col overflow-hidden">
                    <a href="{{ route('articles.show', $featuredArticle) }}" class="relative block aspect-16/10 overflow-hidden bg-cream-100 dark:bg-ink-800">
                        @if ($featuredArticle->thumbnail_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($featuredArticle->thumbnail_path) }}" alt="{{ $featuredArticle->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
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
                            <article class="reveal group flex gap-4 py-4 first:pt-0 last:pb-0" style="--reveal-delay: {{ $loop->index * 80 }}ms">
                                <a href="{{ route('articles.show', $article) }}" class="block aspect-square w-24 flex-none overflow-hidden rounded-lg bg-cream-100 dark:bg-ink-800 sm:w-28">
                                    @if ($article->thumbnail_path)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($article->thumbnail_path) }}" alt="{{ $article->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
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
        @else
            <x-public-site.empty-state class="reveal mt-10">Belum ada artikel yang dipublikasikan.</x-public-site.empty-state>
        @endif
    </div>
</section>

{{-- ============================ AGENDA ============================ --}}
<section id="agenda" class="section scroll-mt-24 bg-cream-50 dark:bg-ink-950">
    <div class="container-x max-w-4xl!">
        <div class="reveal text-center">
            <span class="eyebrow">Jadwal Kegiatan</span>
            <h2 class="section-title mt-3">Agenda Mendatang</h2>
            <span class="title-rule mx-auto"></span>
        </div>

        @if ($upcomingAgendas->isNotEmpty())
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
        @else
            <x-public-site.empty-state class="reveal mt-10">Belum ada agenda mendatang yang dijadwalkan.</x-public-site.empty-state>
        @endif
    </div>
</section>

{{-- ============================ PENGURUS ============================ --}}
<section id="pengurus" class="section scroll-mt-24 bg-white dark:bg-ink-900">
    <div class="container-x">
        <div class="reveal text-center">
            <span class="eyebrow">Struktur Organisasi</span>
            <h2 class="section-title mt-3">Susunan Pengurus</h2>
            <span class="title-rule mx-auto"></span>
            @if ($activePeriod)
                <p class="mt-3 text-ink-500 dark:text-ink-400">Masa Bakti {{ $activePeriod->label() }}</p>
            @endif
        </div>

        @if ($activePeriod && $activePeriod->activeMembers->isNotEmpty())
            @php($members = $activePeriod->activeMembers)
            @php($core = $members->where('division', 'Pengurus Inti')->values())
            @php($divisions = $members->reject(fn ($m) => $m->division === 'Pengurus Inti')->groupBy(fn ($m) => $m->division ?: 'Lainnya'))
            @php($leader = $core->first())
            @php($coreRest = $core->slice(1)->values())
            @php($divisionKeys = $divisions->keys())

            {{-- Pengurus inti: ketua dominan + jajaran inti lain --}}
            @if ($leader)
                <div class="reveal mt-12">
                    <div class="mx-auto max-w-xs">
                        <x-public-site.member-card :member="$leader" featured />
                    </div>
                    @if ($coreRest->isNotEmpty())
                        <div class="mx-auto mt-6 grid max-w-3xl gap-6 sm:grid-cols-3">
                            @foreach ($coreRest as $member)
                                <x-public-site.member-card :member="$member" />
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Pengurus bidang: tab di desktop, accordion di mobile --}}
            @if ($divisions->isNotEmpty())
                @php($tabCount = $divisionKeys->count())

                {{-- Desktop: tabs --}}
                <div x-data="{ tab: 0 }" class="reveal mt-14 hidden lg:block">
                    <div role="tablist" aria-label="Bidang kepengurusan"
                         class="flex flex-wrap gap-1 border-b border-cream-200 dark:border-ink-800"
                         x-on:keydown.arrow-right.prevent="tab = (tab + 1) % {{ $tabCount }}; $refs['t' + tab].focus()"
                         x-on:keydown.arrow-left.prevent="tab = (tab - 1 + {{ $tabCount }}) % {{ $tabCount }}; $refs['t' + tab].focus()">
                        @foreach ($divisionKeys as $i => $key)
                            <button type="button" role="tab" x-ref="t{{ $i }}"
                                    id="pengurus-tab-{{ $i }}" aria-controls="pengurus-panel-{{ $i }}"
                                    :aria-selected="tab === {{ $i }} ? 'true' : 'false'"
                                    :tabindex="tab === {{ $i }} ? 0 : -1"
                                    x-on:click="tab = {{ $i }}"
                                    class="-mb-px rounded-t-lg border-b-2 px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-maroon-600 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-gold-400 dark:focus-visible:ring-offset-ink-900"
                                    :class="tab === {{ $i }} ? 'border-maroon-700 text-maroon-800 dark:border-gold-400 dark:text-gold-400' : 'border-transparent text-ink-500 hover:text-maroon-700 dark:text-ink-400 dark:hover:text-cream-100'">
                                {{ $key }}
                            </button>
                        @endforeach
                    </div>

                    @foreach ($divisions as $key => $bidangMembers)
                        @php($i = $divisionKeys->search($key))
                        <div role="tabpanel" id="pengurus-panel-{{ $i }}" aria-labelledby="pengurus-tab-{{ $i }}"
                             x-show="tab === {{ $i }}" x-cloak class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($bidangMembers as $member)
                                <x-public-site.member-card :member="$member" />
                            @endforeach
                        </div>
                    @endforeach
                </div>

                {{-- Mobile: accordion --}}
                <div x-data="{ open: 0 }" class="reveal mt-10 space-y-3 lg:hidden">
                    @foreach ($divisions as $key => $bidangMembers)
                        @php($i = $divisionKeys->search($key))
                        <div class="surface overflow-hidden">
                            <h3>
                                <button type="button"
                                        x-on:click="open = (open === {{ $i }} ? null : {{ $i }})"
                                        :aria-expanded="open === {{ $i }} ? 'true' : 'false'"
                                        aria-controls="pengurus-acc-{{ $i }}"
                                        class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left font-display text-base font-bold text-maroon-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-maroon-600 dark:text-cream-100 dark:focus-visible:ring-gold-400">
                                    <span>{{ $key }}</span>
                                    <svg class="h-5 w-5 flex-none text-maroon-500 transition-transform dark:text-gold-400"
                                         :class="open === {{ $i }} && 'rotate-180'"
                                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </h3>
                            <div id="pengurus-acc-{{ $i }}" x-show="open === {{ $i }}" x-collapse x-cloak>
                                <div class="grid gap-5 px-5 pb-5 sm:grid-cols-2">
                                    @foreach ($bidangMembers as $member)
                                        <x-public-site.member-card :member="$member" />
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            <x-public-site.empty-state class="reveal mt-10">Susunan pengurus akan segera diperbarui.</x-public-site.empty-state>
        @endif
    </div>
</section>

{{-- ============================ KONTAK ============================ --}}
<section id="kontak" class="section relative isolate scroll-mt-24 overflow-hidden bg-maroon-950 text-cream-50">
    <div class="hero-motif pointer-events-none absolute inset-0 -z-10 opacity-40" aria-hidden="true"></div>
    <div class="container-x">
        <div class="reveal text-center">
            <span class="eyebrow text-gold-400!">Hubungi Kami</span>
            <h2 class="section-title mt-3 text-cream-100!">Kontak &amp; Media Sosial</h2>
            <span class="title-rule mx-auto"></span>
        </div>

        @if ($contact->hasAnyContact() || $contact->map_embed_url)
            <div class="mt-12 grid gap-8 md:grid-cols-2">
                <div class="reveal space-y-6 text-sm">
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

                @if ($contact->map_embed_url)
                    <div class="reveal overflow-hidden rounded-xl border border-cream-100/15">
                        <iframe src="{{ $contact->map_embed_url }}" title="Peta lokasi FPK Kota Malang" class="h-72 w-full md:h-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    </div>
                @endif
            </div>
        @else
            <div class="reveal mx-auto mt-10 max-w-lg rounded-xl border border-dashed border-cream-100/25 px-6 py-12 text-center text-cream-100/70">
                Informasi kontak resmi akan segera diperbarui.
            </div>
        @endif
    </div>
</section>

@endsection
