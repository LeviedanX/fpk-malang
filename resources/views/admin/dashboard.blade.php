@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    @php($hour = (int) now()->format('H'))
    @php($greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam')))
    @php($firstName = \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->first())

    {{-- Welcome hero --}}
    <section class="admin-hero reveal px-6 py-7 sm:px-8 sm:py-8" data-no-auto-motion>
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-300">{{ now()->translatedFormat('l, d F Y') }}</p>
                <h2 class="mt-2 font-display text-2xl font-bold text-cream-50 sm:text-3xl">
                    {{ $greeting }}, <span class="text-gradient-gold">{{ $firstName }}</span> &#128075;
                </h2>
                <p class="mt-2 max-w-xl text-sm leading-relaxed text-cream-100/70">
                    Kelola konten, agenda, dan profil resmi {{ $site->organization_name }} dari satu tempat. Pilih tindakan cepat di bawah untuk mulai.
                </p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:flex-col lg:items-stretch">
                <a href="{{ route('admin.articles.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gold-500 px-4 py-2.5 text-sm font-semibold text-maroon-950 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:bg-gold-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Artikel
                </a>
                <a href="{{ route('admin.agendas.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-cream-50 backdrop-blur transition duration-300 hover:-translate-y-0.5 hover:bg-white/20">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Agenda
                </a>
            </div>
        </div>
    </section>

    {{-- Stat cards --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @php($stats = [
            ['Total Artikel', $articlesTotal, route('admin.articles.index'), 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h7l2 2h5a2 2 0 012 2v10a2 2 0 01-2 2zM7 10h10M7 14h7'],
            ['Artikel Terbit', $articlesPublished, route('admin.articles.index', ['status' => 'published']), 'M5 13l4 4L19 7'],
            ['Total Agenda', $agendasTotal, route('admin.agendas.index'), 'M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z'],
            ['Agenda Mendatang', $agendasUpcoming, route('admin.agendas.index'), 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Anggota Pengurus', $membersTotal, route('admin.members.index'), 'M17 20h5v-2a4 4 0 00-4-4h-1M9 20H2v-2a4 4 0 014-4h3m4-7a4 4 0 11-8 0 4 4 0 018 0z'],
        ])
        @foreach ($stats as [$label, $value, $link, $icon])
            <a href="{{ $link }}" class="reveal admin-stat group flex items-center gap-4 p-5" style="--reveal-delay: {{ ($loop->index % 3) * 65 }}ms">
                <span class="admin-stat-icon">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-medium text-slate-500">{{ $label }}</span>
                    <span class="mt-0.5 block font-display text-3xl font-bold text-maroon-800">{{ $value }}</span>
                </span>
                <svg class="h-5 w-5 flex-none text-slate-300 transition-all duration-300 group-hover:translate-x-1 group-hover:text-maroon-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        @endforeach
    </div>

    {{-- Recent lists --}}
    <div class="grid gap-6 lg:grid-cols-2">
        <x-admin.card title="Artikel Terbaru">
            @forelse ($latestArticles as $article)
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-3 last:border-0">
                    <a href="{{ route('admin.articles.edit', $article) }}" class="min-w-0 truncate text-sm leading-snug text-slate-700 transition hover:text-maroon-700">{{ $article->title }}</a>
                    <span class="flex-none rounded-full px-2 py-0.5 text-xs {{ $article->status->value === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $article->status->label() }}</span>
                </div>
            @empty
                <div class="flex flex-col items-center gap-3 py-8 text-center">
                    <span class="grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h7l2 2h5a2 2 0 012 2v10a2 2 0 01-2 2zM7 10h10M7 14h7"/></svg>
                    </span>
                    <p class="text-sm text-slate-500">Belum ada artikel.</p>
                    <a href="{{ route('admin.articles.create') }}" class="text-sm font-semibold text-maroon-700 hover:underline">Tulis artikel pertama &rarr;</a>
                </div>
            @endforelse
        </x-admin.card>

        <x-admin.card title="Agenda Terdekat">
            @forelse ($nearestAgendas as $agenda)
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-3 last:border-0">
                    <a href="{{ route('admin.agendas.edit', $agenda) }}" class="min-w-0 truncate text-sm leading-snug text-slate-700 transition hover:text-maroon-700">{{ $agenda->title }}</a>
                    <span class="flex-none rounded-full bg-maroon-50 px-2 py-0.5 text-xs font-medium text-maroon-700">{{ $agenda->starts_at->translatedFormat('d M Y') }}</span>
                </div>
            @empty
                <div class="flex flex-col items-center gap-3 py-8 text-center">
                    <span class="grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
                    </span>
                    <p class="text-sm text-slate-500">Belum ada agenda mendatang.</p>
                    <a href="{{ route('admin.agendas.create') }}" class="text-sm font-semibold text-maroon-700 hover:underline">Tambah agenda &rarr;</a>
                </div>
            @endforelse
        </x-admin.card>
    </div>
@endsection
