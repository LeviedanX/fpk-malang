@php
    $home = route('home');
    $isHome = request()->routeIs('home');
    $links = [
        ['beranda', 'Beranda', $home.'#beranda'],
        ['tentang', 'Tentang FPK', $home.'#tentang'],
    ];

    if ($publicContentVisibility['agendas']) {
        $links[] = ['agenda', 'Agenda', $home.'#agenda'];
    }

    if ($publicContentVisibility['gallery']) {
        $links[] = ['galeri', 'Galeri', $home.'#galeri'];
    }

    if ($publicContentVisibility['articles']) {
        $links[] = ['artikel', 'Artikel', $home.'#artikel'];
    }

    if ($publicContentVisibility['management']) {
        $links[] = ['pengurus', 'Pengurus', $home.'#pengurus'];
    }

    if ($publicContentVisibility['contact']) {
        $links[] = ['kontak', 'Kontak', $home.'#kontak'];
    }

    $trackedSections = collect($links)->pluck(0)->values();
@endphp
<header
    x-data="siteNav({{ \Illuminate\Support\Js::from($trackedSections) }})"
    class="fixed inset-x-0 top-0 z-40 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
    :class="[
        scrolled || open
            ? 'border-b border-maroon-100/70 bg-cream-50/90 backdrop-blur-md shadow-sm'
            : 'border-b border-transparent bg-transparent'
    ]"
    @keydown.escape.window="closeMenu()"
>
    <nav class="container-x flex items-center justify-between gap-4 py-3" aria-label="Navigasi utama">
        <a href="{{ $home }}#beranda" class="group flex items-center gap-3 transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] hover:scale-[1.02]">
            @if ($site->logo_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($site->logo_path) }}" alt="Logo {{ $site->organization_name }}" class="h-10 w-auto" width="40" height="40">
            @else
                <span class="grid h-10 w-10 place-items-center overflow-hidden rounded-full bg-white p-1 shadow-sm ring-2 ring-gold-400/40" aria-hidden="true">
                    <img src="{{ asset('assets/images/branding/logo-fpk-48.webp') }}"
                         srcset="{{ asset('assets/images/branding/logo-fpk-48.webp') }} 1x, {{ asset('assets/images/branding/logo-fpk-96.webp') }} 2x"
                         alt="" class="h-full w-full object-contain" width="40" height="40" decoding="async">
                </span>
            @endif
            <span class="leading-tight">
                <span class="block font-display text-base font-bold transition-colors duration-500 ease-[cubic-bezier(0.2,0.8,0.2,1)]"
                      :class="scrolled || open ? 'text-maroon-800' : 'text-cream-50'">
                    {{ $site->abbreviation ?: $site->site_name }}
                </span>
                <span class="block text-[11px] uppercase tracking-wider transition-colors duration-500 ease-[cubic-bezier(0.2,0.8,0.2,1)]"
                      :class="scrolled || open ? 'text-ink-600' : 'text-cream-100/85'">
                    {{ $site->organization_name }}
                </span>
            </span>
        </a>

        <ul class="hidden items-center gap-1 lg:flex">
            @foreach ($links as [$id, $label, $href])
                <li>
                    <a href="{{ $href }}"
                       @if ($isHome)
                           :aria-current="isActive('{{ $id }}') ? 'location' : null"
                           :class="isActive('{{ $id }}')
                               ? (scrolled || open ? 'text-maroon-700' : 'text-gold-400')
                               : (scrolled || open ? 'text-ink-700 hover:text-maroon-700' : 'text-cream-100/90 hover:text-cream-50')"
                       @else
                           :class="scrolled || open
                               ? 'text-ink-700 hover:text-maroon-700'
                               : 'text-cream-100/90 hover:text-cream-50'"
                       @endif
                       class="nav-link relative rounded-md px-3 py-2 text-sm font-semibold transition-colors duration-500 ease-[cubic-bezier(0.2,0.8,0.2,1)]">
                        {{ $label }}
                        @if ($isHome)
                            <span class="absolute inset-x-3 -bottom-0.5 h-0.5 rounded-full bg-gold-500 transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
                                  :class="isActive('{{ $id }}') ? 'scale-x-100' : 'scale-x-0'"></span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="flex items-center gap-2">
            @if ($site->background_music_path && $site->background_music_visible)
                <div
                    x-data="siteMusicPlayer({
                        volume: @js($site->background_music_volume),
                        preferenceVersion: @js($site->background_music_preference_version),
                    })"
                    data-site-music-player
                    data-music-default-playing="{{ $site->background_music_default_playing ? 'on' : 'off' }}"
                    data-music-volume="{{ $site->background_music_volume }}"
                    data-music-preference-version="{{ $site->background_music_preference_version }}"
                    :data-music-playback-state="actuallyPlaying ? 'playing' : (playbackBlocked ? 'blocked' : (playing ? 'starting' : 'off'))"
                    class="contents"
                >
                    <audio x-ref="audio" preload="none" loop data-site-music-audio>
                        <source src="{{ \Illuminate\Support\Facades\Storage::url($site->background_music_path) }}">
                    </audio>
                    <button type="button" @click="toggle()"
                        data-site-music-toggle
                        class="icon-button relative grid h-11 w-11 place-items-center rounded-md"
                        :class="scrolled || open
                            ? 'text-maroon-800 hover:bg-maroon-50'
                            : 'text-cream-50 hover:bg-white/10'"
                        :aria-pressed="actuallyPlaying"
                        :aria-label="!playing ? 'Nyalakan musik latar' : (actuallyPlaying ? 'Matikan musik latar' : 'Putar musik latar')">
                        <span class="sr-only" x-text="!playing ? 'Nyalakan musik latar' : (actuallyPlaying ? 'Matikan musik latar' : 'Putar musik latar')"></span>
                        <svg x-show="playing && actuallyPlaying" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 18V6l10-2v12M9 18a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM19 16a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                        <svg x-show="playing && !actuallyPlaying" x-cloak class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13l10-6.5z"/></svg>
                        <svg x-show="!playing" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 18V6l10-2v12M9 18a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM19 16a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/><path stroke-linecap="round" d="M3 3l18 18"/></svg>
                        <span x-show="playing && !actuallyPlaying" x-cloak class="absolute right-1 top-1 h-1.5 w-1.5 animate-pulse rounded-full bg-gold-400" aria-hidden="true"></span>
                    </button>
                </div>
            @endif

            <button type="button" @click="toggleMenu($event)"
                x-ref="menuToggle"
                class="icon-button grid h-11 w-11 place-items-center rounded-md lg:hidden"
                :class="scrolled || open
                    ? 'text-maroon-800 hover:bg-maroon-50'
                    : 'text-cream-50 hover:bg-white/10'"
                :aria-expanded="open" aria-controls="mobile-menu">
                <span class="sr-only">Menu navigasi</span>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path x-show="!open" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/><path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </nav>

    <div id="mobile-menu" x-ref="mobileMenu" x-show="open" x-cloak x-collapse.duration.350ms
         @keydown.tab="trapMenuFocus($event)"
         class="border-t border-maroon-100 bg-cream-50 lg:hidden">
        <ul class="container-x space-y-1 py-3" @click="closeMenu(false)">
            @foreach ($links as [$id, $label, $href])
                <li>
                    <a href="{{ $href }}"
                       @if ($isHome)
                           :aria-current="isActive('{{ $id }}') ? 'location' : null"
                       @endif
                       class="block rounded-md px-3 py-2.5 text-sm font-medium text-ink-700 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] hover:translate-x-1 hover:bg-maroon-50 hover:text-maroon-700">
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</header>
