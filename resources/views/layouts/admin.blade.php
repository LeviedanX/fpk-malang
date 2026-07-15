<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Panel Admin') &middot; {{ $site->site_name }}</title>
    @if ($site->favicon_path)
        <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::url($site->favicon_path) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased" x-data="{ sidebar: false }">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside
            x-cloak
            class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full transform overflow-y-auto bg-maroon-900 text-cream-100 transition-transform md:relative md:translate-x-0"
            :class="sidebar ? 'translate-x-0' : ''"
        >
            <div class="flex items-center gap-2 border-b border-maroon-800 px-5 py-4">
                <span class="grid h-9 w-9 place-items-center rounded-full bg-cream-50 font-serif font-bold text-maroon-800">F</span>
                <span class="font-serif font-semibold text-cream-50">{{ $site->abbreviation ?: 'FPK Admin' }}</span>
            </div>

            <nav class="px-3 py-4 text-sm" aria-label="Navigasi admin">
                @php($nav = [
                    ['admin.dashboard', 'Dashboard', ['admin.dashboard']],
                    ['admin.profile.edit', 'Profil FPK', ['admin.profile.*']],
                    ['admin.articles.index', 'Artikel', ['admin.articles.*']],
                    ['admin.agendas.index', 'Agenda', ['admin.agendas.*']],
                    ['admin.periods.index', 'Susunan Pengurus', ['admin.periods.*', 'admin.members.*']],
                    ['admin.contact.edit', 'Kontak & Media Sosial', ['admin.contact.*']],
                    ['admin.settings.edit', 'Pengaturan Website', ['admin.settings.*']],
                    ['admin.account.edit', 'Akun Admin', ['admin.account.*']],
                ])
                <ul class="space-y-1">
                    @foreach ($nav as [$route, $label, $patterns])
                        <li>
                            <a
                                href="{{ route($route) }}"
                                @class([
                                    'block rounded-md px-3 py-2 font-medium transition',
                                    'bg-maroon-700 text-white' => request()->routeIs(...$patterns),
                                    'text-cream-100/80 hover:bg-maroon-800 hover:text-white' => ! request()->routeIs(...$patterns),
                                ])
                                @if (request()->routeIs(...$patterns)) aria-current="page" @endif
                            >
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </aside>

        {{-- Backdrop for mobile --}}
        <div x-show="sidebar" x-cloak @click="sidebar = false" class="fixed inset-0 z-30 bg-black/40 md:hidden" aria-hidden="true"></div>

        {{-- Main --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 py-3">
                <button type="button" @click="sidebar = !sidebar" class="rounded-md p-2 text-maroon-800 hover:bg-slate-100 md:hidden" aria-label="Buka menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <h1 class="truncate font-serif text-lg font-semibold text-slate-800">@yield('heading', 'Panel Admin')</h1>

                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="hidden text-sm text-slate-500 hover:text-maroon-700 sm:inline">Lihat situs</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-md bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 md:px-8">
                <div class="mx-auto max-w-5xl space-y-6">
                    <x-admin.flash />
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>
</html>
