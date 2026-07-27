<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4f1c20">
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">html{background:#2e0f12}</style>

    {{-- Aktifkan enhancement berbasis JavaScript tanpa mengubah tema visual. --}}
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">document.documentElement.classList.add('js');</script>

    @php
        $metaTitle = trim($__env->yieldContent('title')) ?: ($site->default_meta_title ?: $site->site_name);
        $metaDescription = \Illuminate\Support\Str::limit(
            trim(strip_tags($__env->yieldContent('meta_description'))) ?: ($site->default_meta_description ?: $site->tagline),
            160,
            ''
        );
        $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
        $robots = trim($__env->yieldContent('robots'))
            ?: 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
        $ogType = trim($__env->yieldContent('og_type')) ?: 'website';
        $ogImagePath = trim($__env->yieldContent('og_image'))
            ?: ($site->default_og_image_path
                ? \Illuminate\Support\Facades\Storage::url($site->default_og_image_path)
                : '');
        $ogImage = $ogImagePath === ''
            ? null
            : (\Illuminate\Support\Str::startsWith($ogImagePath, ['http://', 'https://'])
                ? $ogImagePath
                : url($ogImagePath));
    @endphp

    <title>{{ $metaTitle }}</title>
    @if ($metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    <meta name="robots" content="{{ $robots }}">
    <meta name="googlebot" content="{{ $robots }}">
    @if ($site->default_meta_keywords)
        <meta name="keywords" content="{{ $site->default_meta_keywords }}">
    @endif
    <link rel="canonical" href="{{ $canonical }}">

    <meta property="og:site_name" content="{{ $site->site_name }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:title" content="{{ $metaTitle }}">
    @if ($metaDescription)
        <meta property="og:description" content="{{ $metaDescription }}">
    @endif
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ $canonical }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:alt" content="{{ $metaTitle }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    @if ($metaDescription)
        <meta name="twitter:description" content="{{ $metaDescription }}">
    @endif
    @if ($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
        <meta name="twitter:image:alt" content="{{ $metaTitle }}">
    @endif

    @php
        $faviconUrl = $site->favicon_path
            ? \Illuminate\Support\Facades\Storage::url($site->favicon_path)
            : ($site->logo_path
                ? \Illuminate\Support\Facades\Storage::url($site->logo_path)
                : asset('assets/images/branding/favicon-fpk.png'));
    @endphp
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

    @if (isset($profile))
        @php
            $publicDesktopBackgroundUrl = $profile->hero_background_path
                ? \Illuminate\Support\Facades\Storage::url($profile->hero_background_path)
                : null;
            $publicMobileBackgroundUrl = $profile->hero_mobile_background_path
                ? \Illuminate\Support\Facades\Storage::url($profile->hero_mobile_background_path)
                : $publicDesktopBackgroundUrl;
        @endphp

        @if ($publicDesktopBackgroundUrl && $publicMobileBackgroundUrl === $publicDesktopBackgroundUrl)
            <link rel="preload" as="image" href="{{ $publicDesktopBackgroundUrl }}" fetchpriority="high" data-public-background-preload>
        @elseif ($publicDesktopBackgroundUrl)
            <link rel="preload" as="image" href="{{ $publicDesktopBackgroundUrl }}" media="(min-width: 1024px)" fetchpriority="high" data-public-background-preload="desktop">
            @if ($publicMobileBackgroundUrl)
                <link rel="preload" as="image" href="{{ $publicMobileBackgroundUrl }}" media="(max-width: 1023px)" fetchpriority="high" data-public-background-preload="mobile">
            @endif
        @endif
    @endif

    @include('public-site.partials.seo-jsonld')

    @yield('pagination_links')
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
