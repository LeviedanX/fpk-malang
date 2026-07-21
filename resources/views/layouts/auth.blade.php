<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Masuk') &middot; {{ $site->site_name }}</title>
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
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-shell relative min-h-dvh overflow-x-hidden bg-maroon-950 font-sans text-slate-800 antialiased">
    <x-admin.desktop-only-notice />

    {{-- Latar dekoratif berlapis --}}
    <div class="pointer-events-none fixed inset-0 hidden lg:block" aria-hidden="true">
        <div class="hero-motif absolute inset-0 opacity-[0.14]"></div>
        <div class="hero-glow absolute inset-0"></div>
        <div class="float-slow absolute -left-44 top-[-14%] h-160 w-160 rounded-full bg-maroon-600/25 blur-3xl"></div>
        <div class="absolute -right-40 bottom-[-20%] h-152 w-152 rounded-full bg-gold-500/12 blur-3xl"></div>
        <div class="absolute left-1/2 top-1/2 h-208 w-208 -translate-x-1/2 -translate-y-1/2 rounded-full bg-[radial-gradient(closest-side,rgba(217,164,65,0.10),transparent)]"></div>
        <div class="absolute inset-0 bg-linear-to-b from-black/25 via-transparent to-black/50"></div>
    </div>

    <main class="admin-desktop-content relative mx-auto hidden min-h-dvh w-full items-center justify-center px-8 py-12 lg:flex" data-admin-desktop-content>

        <section class="auth-card relative w-full max-w-sm overflow-hidden rounded-3xl border border-white/12 bg-white shadow-2xl shadow-black/40">
            {{-- Aksen emas tipis di tepi atas kartu --}}
            <span class="absolute inset-x-0 top-0 h-1 bg-linear-to-r from-maroon-700 via-gold-500 to-maroon-700" aria-hidden="true"></span>

            <div class="px-8 pb-9 pt-10 sm:px-11">
                {{-- Identitas ringkas: logo + sistem --}}
                <div class="flex flex-col items-center text-center">
                    <span class="auth-logo grid h-19 w-19 place-items-center rounded-full bg-white p-2 shadow-lg ring-1 ring-gold-400/45">
                        <img src="{{ $faviconUrl }}" alt="Logo {{ $site->abbreviation ?: $site->site_name }}" class="h-full w-full object-contain">
                    </span>
                    <p class="mt-4 inline-flex items-center gap-2 text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-maroon-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-gold-500"></span>
                        Sistem Administrasi
                    </p>
                </div>

                @yield('content')

                {{-- Tombol kembali ke beranda --}}
                <div class="mt-8 border-t border-slate-100 pt-5 text-center">
                    <a href="{{ route('home') }}" class="auth-back group inline-flex items-center gap-1.5 text-sm font-semibold text-maroon-600 transition-colors hover:text-maroon-800">
                        <svg class="h-4 w-4 transition-transform duration-300 ease-out group-hover:-translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        <span>Kembali ke Beranda</span>
                    </a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
