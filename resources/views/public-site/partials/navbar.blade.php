@php($home = route('home'))
@php($isHome = request()->routeIs('home'))
@php($links = [
    ['beranda', 'Beranda', $home.'#beranda'],
    ['tentang', 'Tentang FPK', $home.'#tentang'],
    ['artikel', 'Artikel', $isHome ? $home.'#artikel' : route('articles.index')],
    ['agenda', 'Agenda', $isHome ? $home.'#agenda' : route('agendas.index')],
    ['pengurus', 'Pengurus', $home.'#pengurus'],
])
<header
    x-data="siteNav(['beranda','tentang','artikel','agenda','pengurus','kontak'])"
    class="fixed inset-x-0 top-0 z-40 transition-all duration-300"
    :class="scrolled || open
        ? 'border-b border-maroon-100/70 bg-cream-50/90 backdrop-blur-md shadow-sm dark:border-ink-800 dark:bg-ink-950/90'
        : 'border-b border-transparent bg-transparent'"
>
    <nav class="container-x flex items-center justify-between gap-4 py-3" aria-label="Navigasi utama">
        <a href="{{ $home }}#beranda" class="group flex items-center gap-3">
            @if ($site->logo_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($site->logo_path) }}" alt="Logo {{ $site->organization_name }}" class="h-10 w-auto" width="40" height="40">
            @else
                <span class="grid h-10 w-10 place-items-center rounded-full bg-maroon-700 font-display text-lg font-bold text-cream-50 ring-2 ring-gold-400/40" aria-hidden="true">F</span>
            @endif
            <span class="leading-tight">
                <span class="block font-display text-base font-bold text-maroon-800 dark:text-cream-100">{{ $site->abbreviation ?: 'FPK Kota Malang' }}</span>
                <span class="block text-[11px] uppercase tracking-wider text-ink-400">Forum Pembauran Kebangsaan</span>
            </span>
        </a>

        <ul class="hidden items-center gap-1 lg:flex">
            @foreach ($links as [$id, $label, $href])
                <li>
                    <a href="{{ $href }}"
                       @if ($isHome) :class="isActive('{{ $id }}') ? 'text-maroon-700 dark:text-gold-400' : 'text-ink-600 dark:text-ink-300'" @endif
                       class="relative rounded-md px-3 py-2 text-sm font-medium transition hover:text-maroon-700 dark:hover:text-gold-400 {{ $isHome ? '' : 'text-ink-600 dark:text-ink-300' }}">
                        {{ $label }}
                        @if ($isHome)
                            <span class="absolute inset-x-3 -bottom-0.5 h-0.5 rounded-full bg-gold-500 transition-transform duration-300"
                                  :class="isActive('{{ $id }}') ? 'scale-x-100' : 'scale-x-0'"></span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="flex items-center gap-2">
            <button type="button" @click="$store.theme.toggle()"
                class="grid h-9 w-9 place-items-center rounded-md text-ink-500 transition hover:bg-maroon-50 hover:text-maroon-700 dark:text-ink-300 dark:hover:bg-ink-800"
                :aria-label="$store.theme.dark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'">
                <svg x-show="!$store.theme.dark" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                <svg x-show="$store.theme.dark" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36 6.36l-1.42-1.42M7.06 7.06L5.64 5.64m12.72 0l-1.42 1.42M7.06 16.94l-1.42 1.42M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>

            <a href="{{ $home }}#kontak" class="hidden btn-primary px-4! py-2! sm:inline-flex">Kontak</a>

            <button type="button" @click="open = !open"
                class="grid h-9 w-9 place-items-center rounded-md text-maroon-800 hover:bg-maroon-50 dark:text-cream-100 dark:hover:bg-ink-800 lg:hidden"
                :aria-expanded="open" aria-controls="mobile-menu">
                <span class="sr-only">Menu navigasi</span>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path x-show="!open" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/><path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </nav>

    <div id="mobile-menu" x-show="open" x-cloak x-collapse.duration.250ms class="border-t border-maroon-100 bg-cream-50 lg:hidden dark:border-ink-800 dark:bg-ink-950">
        <ul class="container-x space-y-1 py-3" @click="open = false">
            @foreach ($links as [$id, $label, $href])
                <li><a href="{{ $href }}" class="block rounded-md px-3 py-2.5 text-sm font-medium text-ink-700 hover:bg-maroon-50 hover:text-maroon-700 dark:text-ink-200 dark:hover:bg-ink-800">{{ $label }}</a></li>
            @endforeach
            <li><a href="{{ $home }}#kontak" class="mt-1 block rounded-md bg-maroon-700 px-3 py-2.5 text-center text-sm font-semibold text-cream-50">Kontak</a></li>
        </ul>
    </div>
</header>
