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
    <script>document.documentElement.classList.add('js');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-shell relative min-h-dvh overflow-x-hidden bg-maroon-950 font-sans text-slate-800 antialiased">
    <div class="hero-motif parallax-layer pointer-events-none fixed inset-0 opacity-50" data-parallax="0.012" aria-hidden="true"></div>
    <div class="hero-glow parallax-layer pointer-events-none fixed inset-0" data-parallax="0.025" aria-hidden="true"></div>
    <div class="pointer-events-none fixed -left-24 top-1/4 h-72 w-72 rounded-full bg-gold-400/10 blur-3xl" aria-hidden="true"></div>

    <main class="relative mx-auto grid min-h-dvh w-full max-w-6xl items-center gap-8 px-4 py-8 sm:px-6 sm:py-12 lg:grid-cols-[1fr_0.82fr] lg:px-8">
        <section class="reveal reveal-left hidden max-w-xl text-cream-50 lg:block">
            <span class="eyebrow text-gold-400!">Forum Pembauran Kebangsaan</span>
            <h1 class="mt-5 font-display text-5xl font-extrabold leading-tight">Kelola informasi publik dengan rapi dan bertanggung jawab.</h1>
            <p class="mt-5 max-w-lg text-base leading-relaxed text-cream-100/70">Panel terpadu untuk artikel, agenda, profil organisasi, kepengurusan, kontak, dan identitas website FPK Kota Malang.</p>
            <div class="mt-8 flex items-center gap-3 text-sm text-cream-100/60">
                <span class="h-px w-12 bg-gold-400/60"></span>
                Akses khusus administrator
            </div>
        </section>

        <div class="w-full max-w-md justify-self-center">
            <div class="reveal reveal-scale mb-5 text-center lg:hidden">
                <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-cream-50 font-display text-2xl font-bold text-maroon-800 ring-4 ring-gold-400/20">F</span>
                <h1 class="mt-3 font-display text-xl font-semibold text-cream-50">{{ $site->organization_name }}</h1>
                <p class="text-sm text-cream-100/60">Panel Administrator</p>
            </div>

            <section class="auth-card rounded-2xl border border-white/15 bg-white/96 p-5 shadow-2xl shadow-black/30 backdrop-blur-xl sm:p-8">
                <div class="mb-6 hidden items-center gap-3 lg:flex">
                    <span class="grid h-11 w-11 place-items-center rounded-full bg-maroon-800 font-display text-lg font-bold text-cream-50 ring-2 ring-gold-400/30">F</span>
                    <div class="min-w-0">
                        <p class="truncate font-display font-semibold text-maroon-900">{{ $site->abbreviation ?: 'FPK Kota Malang' }}</p>
                        <p class="text-xs uppercase tracking-wider text-slate-400">Panel Administrator</p>
                    </div>
                </div>
                @yield('content')
            </section>

            <p class="reveal mt-5 text-center text-xs text-cream-100/60" style="--reveal-delay: 160ms">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 transition-all duration-300 hover:-translate-x-1 hover:text-cream-50">
                    <span aria-hidden="true">&larr;</span> Kembali ke situs utama
                </a>
            </p>
        </div>
    </main>
</body>
</html>
