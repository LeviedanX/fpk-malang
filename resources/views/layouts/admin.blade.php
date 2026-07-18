<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Panel Admin') &middot; {{ $site->site_name }}</title>
    @php($faviconUrl = $site->favicon_path
        ? \Illuminate\Support\Facades\Storage::url($site->favicon_path)
        : ($site->logo_path
            ? \Illuminate\Support\Facades\Storage::url($site->logo_path)
            : asset('assets/images/branding/logo-fpk.png')))
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    <script>
        document.documentElement.classList.add('js');
        if (/Macintosh/i.test(navigator.userAgent) && navigator.maxTouchPoints > 1) {
            document.documentElement.classList.add('admin-mobile-device');
        }
        /* Pulihkan status sidebar sebelum paint pertama agar tidak berkedip. */
        try {
            if (localStorage.getItem('adminSidebar') === 'closed') {
                document.documentElement.classList.add('admin-nav-collapsed');
            }
        } catch (e) {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-shell min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">
    <x-admin.desktop-only-notice />

    <div class="admin-desktop-content hidden min-h-screen lg:block" data-admin-desktop-content>
        <div class="scroll-progress" aria-hidden="true"><span data-scroll-progress></span></div>

        <aside
            id="admin-sidebar"
            class="admin-sidebar fixed left-0 top-0 z-40 flex h-screen w-72 flex-col overflow-y-auto bg-maroon-950 text-cream-100 xl:w-80"
            aria-label="Panel navigasi admin"
        >
            @php($adminLogoUrl = $site->logo_path
                ? \Illuminate\Support\Facades\Storage::url($site->logo_path)
                : asset('assets/images/branding/logo-fpk.png'))
            <div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
                <a href="{{ route('admin.dashboard') }}" class="group flex min-w-0 flex-1 items-center gap-3">
                    <span class="grid h-11 w-11 flex-none place-items-center overflow-hidden rounded-full bg-cream-50 ring-2 ring-gold-400/30 transition-transform duration-300 group-hover:scale-105">
                        <img src="{{ $adminLogoUrl }}" alt="Logo {{ $site->organization_name ?: 'FPK Kota Malang' }}" class="h-9 w-9 object-contain" width="36" height="36">
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate font-display font-semibold text-cream-50">{{ $site->abbreviation ?: 'FPK Admin' }}</span>
                        <span class="block text-[10px] uppercase tracking-[0.2em] text-cream-100/50">Panel Administrator</span>
                    </span>
                </a>
            </div>

            <nav class="flex-1 px-3 py-5 text-sm" aria-label="Navigasi admin">
                @php($navGroups = [
                    'Menu Utama' => [
                        ['admin.dashboard', 'Dashboard', ['admin.dashboard'], 'M3 13h8V3H3v10zm10 8h8V11h-8v10zm0-18v6h8V3h-8zM3 21h8v-6H3v6z'],
                    ],
                    'Konten' => [
                        ['admin.profile.edit', 'Profil FPK', ['admin.profile.*'], 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5A4.5 4.5 0 003 9.5V18c0-1.657 2.015-3 4.5-3 1.746 0 3.332.477 4.5 1.253m0-10C13.168 5.477 14.754 5 16.5 5A4.5 4.5 0 0121 9.5V18c0-1.657-2.015-3-4.5-3-1.746 0-3.332.477-4.5 1.253'],
                        ['admin.articles.index', 'Artikel', ['admin.articles.*'], 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h7l2 2h5a2 2 0 012 2v10a2 2 0 01-2 2zM7 10h10M7 14h7'],
                        ['admin.agendas.index', 'Agenda', ['admin.agendas.*'], 'M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z'],
                        ['admin.periods.index', 'Susunan Pengurus', ['admin.periods.*', 'admin.members.*'], 'M17 20h5v-2a4 4 0 00-4-4h-1M9 20H2v-2a4 4 0 014-4h3m4-7a4 4 0 11-8 0 4 4 0 018 0zm8 3a3 3 0 10-2.83-4'],
                    ],
                    'Pengaturan' => [
                        ['admin.contact.edit', 'Kontak & Media Sosial', ['admin.contact.*'], 'M3 5a2 2 0 012-2h3l2 5-2 1a14 14 0 007 7l1-2 5 2v3a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
                        ['admin.settings.edit', 'Pengaturan Website', ['admin.settings.*'], 'M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7zM19.4 15a1.7 1.7 0 00.34 1.88l.06.06-2.12 2.12-.06-.06a1.7 1.7 0 00-1.88-.34 1.7 1.7 0 00-1.03 1.56V20h-3v-.08a1.7 1.7 0 00-1.03-1.56 1.7 1.7 0 00-1.88.34l-.06.06-2.12-2.12.06-.06A1.7 1.7 0 007 15.4 1.7 1.7 0 005.44 14H5v-3h.44A1.7 1.7 0 007 9.6a1.7 1.7 0 00-.34-1.88l-.06-.06 2.12-2.12.06.06A1.7 1.7 0 0010.66 6 1.7 1.7 0 0011.7 4.44V4h3v.44A1.7 1.7 0 0015.74 6a1.7 1.7 0 001.88-.34l.06-.06 2.12 2.12-.06.06A1.7 1.7 0 0019.4 9.6 1.7 1.7 0 0020.96 11H21v3h-.04A1.7 1.7 0 0019.4 15z'],
                        ['admin.account.edit', 'Akun Admin', ['admin.account.*'], 'M5.121 17.804A9 9 0 1118.88 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
                    ],
                ])
                @foreach ($navGroups as $groupLabel => $items)
                    <div class="admin-nav-group">
                        <p class="admin-nav-group-label">{{ $groupLabel }}</p>
                        <ul class="space-y-1">
                            @foreach ($items as [$route, $label, $patterns, $icon])
                                <li>
                                    <a
                                        href="{{ route($route) }}"
                                        @class([
                                            'admin-nav-link group relative flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium',
                                            'bg-white/12 text-white shadow-sm ring-1 ring-white/10' => request()->routeIs(...$patterns),
                                            'text-cream-100/70 hover:bg-white/8 hover:text-white' => ! request()->routeIs(...$patterns),
                                        ])
                                        @if (request()->routeIs(...$patterns)) aria-current="page" @endif
                                    >
                                        <svg class="h-5 w-5 flex-none text-gold-400/80 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                                        <span>{{ $label }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>

            <div class="admin-sidebar-foot mt-auto border-t border-white/10 px-3 py-4">
                <p class="px-3 text-[10px] uppercase tracking-[0.18em] text-cream-100/35">&copy; {{ date('Y') }} {{ $site->abbreviation ?: 'FPK Malang' }}</p>
            </div>
        </aside>

        <div class="admin-content-col flex min-h-screen flex-col">
            <header class="admin-topbar sticky top-0 z-20 flex min-h-16 items-center justify-between gap-2 border-b border-slate-200/80 bg-white/90 px-3 py-3 shadow-sm backdrop-blur-xl sm:gap-4 sm:px-5 lg:px-8">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <button type="button" class="admin-hamburger" data-admin-nav-toggle aria-label="Buka/tutup navigasi" aria-controls="admin-sidebar">
                        <span class="admin-hamburger-box" aria-hidden="true">
                            <span class="admin-hamburger-bar"></span>
                            <span class="admin-hamburger-bar"></span>
                            <span class="admin-hamburger-bar"></span>
                        </span>
                    </button>
                    <div class="min-w-0">
                        <p class="hidden text-[10px] font-semibold uppercase tracking-[0.18em] text-maroon-500 sm:block">Panel Administrator</p>
                        <h1 class="truncate font-display text-base font-semibold text-slate-800 sm:text-lg">@yield('heading', 'Panel Admin')</h1>
                    </div>
                </div>

                <div class="flex flex-none items-center gap-2 sm:gap-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="admin-button admin-button-secondary px-3! py-2!">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m12-8h4a2 2 0 012 2v12a2 2 0 01-2 2h-4"/></svg>
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-3 py-5 sm:px-5 sm:py-6 lg:px-8 lg:py-8">
                <div class="mx-auto max-w-6xl space-y-5 sm:space-y-6" data-admin-content data-motion-children>
                    <x-admin.flash />
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <x-admin.confirm-modal />

    <script>
        (function () {
            var root = document.documentElement;
            document.addEventListener('click', function (event) {
                if (!event.target.closest('[data-admin-nav-toggle]')) {
                    return;
                }
                root.classList.toggle('admin-nav-collapsed');
                try {
                    localStorage.setItem('adminSidebar', root.classList.contains('admin-nav-collapsed') ? 'closed' : 'open');
                } catch (e) {}
            });
        })();
    </script>
</body>
</html>
