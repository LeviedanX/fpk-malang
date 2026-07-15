@extends('layouts.admin')

@section('title', 'Artikel')
@section('heading', 'Artikel')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.articles.index') }}" class="flex flex-wrap gap-2">
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari judul..."
                class="rounded-md border-slate-300 text-sm shadow-sm focus:border-maroon-600 focus:ring-maroon-600">
            <select name="status" class="rounded-md border-slate-300 text-sm shadow-sm focus:border-maroon-600 focus:ring-maroon-600">
                <option value="">Semua status</option>
                <option value="published" @selected($status === 'published')>Terbit</option>
                <option value="draft" @selected($status === 'draft')>Draft</option>
            </select>
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Filter</button>
        </form>

        <a href="{{ route('admin.articles.create') }}" class="rounded-md bg-maroon-700 px-4 py-2 text-sm font-medium text-cream-50 hover:bg-maroon-800">+ Tambah Artikel</a>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Waktu Terbit</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($articles as $article)
                    <tr>
                        <td class="px-4 py-3">
                            <span class="font-medium text-slate-800">{{ $article->title }}</span>
                            @if ($article->is_featured)
                                <span class="ml-2 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.8 6.2 20.9l1.1-6.5L2.6 9.8l6.5-.9z"/></svg>
                                    Unggulan
                                </span>
                            @endif
                            <span class="block text-xs text-slate-400">{{ $article->slug }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs {{ $article->status->value === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $article->status->label() }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ optional($article->published_at)->translatedFormat('d M Y H.i') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.articles.edit', $article) }}" class="rounded border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">Ubah</a>
                                <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Hapus artikel ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded border border-rose-300 px-3 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center text-slate-500">Belum ada artikel. <a href="{{ route('admin.articles.create') }}" class="text-maroon-700 hover:underline">Tambah artikel pertama</a>.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $articles->links() }}</div>
@endsection
