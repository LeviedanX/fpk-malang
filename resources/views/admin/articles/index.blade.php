@extends('layouts.admin')

@section('title', 'Artikel')
@section('heading', 'Artikel')

@section('content')
    <div class="admin-toolbar">
        <form method="GET" action="{{ route('admin.articles.index') }}" class="admin-filter">
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari judul..." class="form-control text-sm sm:min-w-56">
            <select name="status" class="form-control text-sm sm:w-auto">
                <option value="">Semua status</option>
                <option value="published" @selected($status === 'published')>Terbit</option>
                <option value="draft" @selected($status === 'draft')>Draft</option>
            </select>
            <button type="submit" class="admin-button admin-button-dark">Filter</button>
        </form>

        <a href="{{ route('admin.articles.create') }}" class="admin-button admin-button-primary">+ Tambah Artikel</a>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table divide-y divide-slate-200">
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
                        <td data-label="Judul" class="px-4 py-3">
                            <div class="min-w-0">
                                <span class="font-medium text-slate-800">{{ $article->title }}</span>
                                @if ($article->is_featured)
                                    <span class="ml-2 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.8 6.2 20.9l1.1-6.5L2.6 9.8l6.5-.9z"/></svg>
                                        Unggulan
                                    </span>
                                @endif
                                <span class="block break-all text-xs text-slate-400">{{ $article->slug }}</span>
                            </div>
                        </td>
                        <td data-label="Status" class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs {{ $article->status->value === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $article->status->label() }}</span>
                        </td>
                        <td data-label="Waktu Terbit" class="px-4 py-3 text-slate-600">{{ optional($article->published_at)->translatedFormat('d M Y H.i') ?? '—' }}</td>
                        <td data-label="Aksi" class="px-4 py-3">
                            <div class="admin-actions">
                                <a href="{{ route('admin.articles.edit', $article) }}" class="admin-action">Ubah</a>
                                <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Hapus artikel ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-action admin-action-danger">Hapus</button>
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
