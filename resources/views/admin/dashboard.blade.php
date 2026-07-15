@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @php($stats = [
            ['Total Artikel', $articlesTotal, route('admin.articles.index')],
            ['Artikel Terbit', $articlesPublished, route('admin.articles.index', ['status' => 'published'])],
            ['Total Agenda', $agendasTotal, route('admin.agendas.index')],
            ['Agenda Mendatang', $agendasUpcoming, route('admin.agendas.index')],
            ['Anggota Pengurus', $membersTotal, route('admin.members.index')],
        ])
        @foreach ($stats as [$label, $value, $link])
            <a href="{{ $link }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:border-maroon-300 hover:shadow">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-1 font-serif text-3xl font-bold text-maroon-800">{{ $value }}</p>
            </a>
        @endforeach
    </div>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.articles.create') }}" class="rounded-md bg-maroon-700 px-4 py-2 text-sm font-medium text-cream-50 hover:bg-maroon-800">+ Tambah Artikel</a>
        <a href="{{ route('admin.agendas.create') }}" class="rounded-md bg-maroon-700 px-4 py-2 text-sm font-medium text-cream-50 hover:bg-maroon-800">+ Tambah Agenda</a>
        <a href="{{ route('admin.members.create') }}" class="rounded-md border border-maroon-300 px-4 py-2 text-sm font-medium text-maroon-700 hover:bg-maroon-50">+ Tambah Pengurus</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-admin.card title="Artikel Terbaru">
            @forelse ($latestArticles as $article)
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-2 last:border-0">
                    <a href="{{ route('admin.articles.edit', $article) }}" class="truncate text-sm text-slate-700 hover:text-maroon-700">{{ $article->title }}</a>
                    <span class="flex-none rounded-full px-2 py-0.5 text-xs {{ $article->status->value === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $article->status->label() }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada artikel.</p>
            @endforelse
        </x-admin.card>

        <x-admin.card title="Agenda Terdekat">
            @forelse ($nearestAgendas as $agenda)
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-2 last:border-0">
                    <a href="{{ route('admin.agendas.edit', $agenda) }}" class="truncate text-sm text-slate-700 hover:text-maroon-700">{{ $agenda->title }}</a>
                    <span class="flex-none text-xs text-slate-500">{{ $agenda->starts_at->translatedFormat('d M Y') }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada agenda mendatang.</p>
            @endforelse
        </x-admin.card>
    </div>
@endsection
