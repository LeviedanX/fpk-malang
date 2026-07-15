<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Masuk') &middot; {{ $site->site_name }}</title>
    @if ($site->favicon_path)
        <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::url($site->favicon_path) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-maroon-900 px-4 font-sans text-slate-800 antialiased">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-cream-50 font-serif text-2xl font-bold text-maroon-800">F</span>
            <h1 class="mt-3 font-serif text-xl font-semibold text-cream-50">{{ $site->organization_name }}</h1>
            <p class="text-sm text-cream-100/70">Panel Administrator</p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-lg sm:p-8">
            @yield('content')
        </div>

        <p class="mt-6 text-center text-xs text-cream-100/60">
            <a href="{{ route('home') }}" class="hover:text-cream-100">&larr; Kembali ke situs</a>
        </p>
    </div>
</body>
</html>
