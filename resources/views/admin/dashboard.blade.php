@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @php($stats = [
            ['Total Artikel', $articlesTotal, route('admin.articles.index'), 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h7l2 2h5a2 2 0 012 2v10a2 2 0 01-2 2zM7 10h10M7 14h7'],
            ['Artikel Terbit', $articlesPublished, route('admin.articles.index', ['status' => 'published']), 'M5 13l4 4L19 7'],
            ['Total Agenda', $agendasTotal, route('admin.agendas.index'), 'M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z'],
            ['Agenda Mendatang', $agendasUpcoming, route('admin.agendas.index'), 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Anggota Pengurus', $membersTotal, route('admin.members.index'), 'M17 20h5v-2a4 4 0 00-4-4h-1M9 20H2v-2a4 4 0 014-4h3m4-7a4 4 0 11-8 0 4 4 0 018 0z'],
        ])
        @foreach ($stats as [$label, $value, $link, $icon])
            <a href="{{ $link }}" class="reveal admin-card group flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm" style="--reveal-delay: {{ ($loop->index % 3) * 65 }}ms">
                <span class="grid h-12 w-12 flex-none place-items-center rounded-xl bg-maroon-50 text-maroon-700 transition-all duration-300 group-hover:scale-105 group-hover:bg-maroon-700 group-hover:text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </span>
                <span class="min-w-0">
                    <span class="block text-sm text-slate-500">{{ $label }}</span>
                    <span class="mt-0.5 block font-display text-3xl font-bold text-maroon-800">{{ $value }}</span>
                </span>
            </a>
        @endforeach
    </div>

    <div class="grid gap-2 sm:flex sm:flex-wrap sm:gap-3">
        <a href="{{ route('admin.articles.create') }}" class="admin-button admin-button-primary">+ Tambah Artikel</a>
        <a href="{{ route('admin.agendas.create') }}" class="admin-button admin-button-primary">+ Tambah Agenda</a>
        <a href="{{ route('admin.members.create') }}" class="admin-button admin-button-secondary">+ Tambah Pengurus</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-admin.card title="Artikel Terbaru">
            @forelse ($latestArticles as $article)
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 py-3 last:border-0">
                    <a href="{{ route('admin.articles.edit', $article) }}" class="min-w-0 text-sm leading-snug text-slate-700 transition hover:text-maroon-700">{{ $article->title }}</a>
                    <span class="flex-none rounded-full px-2 py-0.5 text-xs {{ $article->status->value === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $article->status->label() }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada artikel.</p>
            @endforelse
        </x-admin.card>

        <x-admin.card title="Agenda Terdekat">
            @forelse ($nearestAgendas as $agenda)
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 py-3 last:border-0">
                    <a href="{{ route('admin.agendas.edit', $agenda) }}" class="min-w-0 text-sm leading-snug text-slate-700 transition hover:text-maroon-700">{{ $agenda->title }}</a>
                    <span class="flex-none text-xs text-slate-500">{{ $agenda->starts_at->translatedFormat('d M Y') }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada agenda mendatang.</p>
            @endforelse
        </x-admin.card>
    </div>
@endsection
