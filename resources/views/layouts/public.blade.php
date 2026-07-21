<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4f1c20">

    {{-- Terapkan tema sebelum paint agar tidak ada kedip (FOUC). --}}
    <script>
        (function () {
            document.documentElement.classList.add('js');
            try {
                if (localStorage.getItem('theme') === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>

    @php($metaTitle = trim($__env->yieldContent('title')) ?: ($site->default_meta_title ?: $site->site_name))
    @php($metaDescription = trim($__env->yieldContent('meta_description')) ?: $site->default_meta_description)
    @php($canonical = trim($__env->yieldContent('canonical')) ?: url()->current())
    @php($ogImage = $site->default_og_image_path ? \Illuminate\Support\Facades\Storage::url($site->default_og_image_path) : null)

    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    @if ($site->default_meta_keywords)
        <meta name="keywords" content="{{ $site->default_meta_keywords }}">
    @endif
    <link rel="canonical" href="{{ $canonical }}">

    <meta property="og:site_name" content="{{ $site->site_name }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonical }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">

    @php($faviconUrl = $site->favicon_path
        ? \Illuminate\Support\Facades\Storage::url($site->favicon_path)
        : ($site->logo_path
            ? \Illuminate\Support\Facades\Storage::url($site->logo_path)
            : asset('assets/images/branding/logo-fpk.png')))
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

    @include('public-site.partials.seo-jsonld')

    @yield('head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="page-shell min-h-screen font-sans">
    <div class="scroll-progress" aria-hidden="true">
        <span data-scroll-progress></span>
    </div>

    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-maroon-700 focus:px-4 focus:py-2 focus:text-white">
        Lewati ke konten utama
    </a>

    @include('public-site.partials.navbar')

    <main id="konten">
        @yield('content')
    </main>

    @include('public-site.partials.footer')
</body>
</html>
