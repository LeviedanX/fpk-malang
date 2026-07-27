@extends('layouts.admin')

@section('title', 'Arsip Artikel')
@section('heading', 'Arsip Artikel')

@section('content')
    <div class="admin-toolbar">
        <form method="GET" action="{{ route('admin.articles.archive') }}" class="admin-filter">
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari judul arsip..." class="form-control text-sm sm:min-w-56">
            <button type="submit" class="admin-button admin-button-dark">Cari</button>
        </form>

        <a href="{{ route('admin.articles.index') }}" class="admin-button admin-button-secondary">&larr; Artikel Aktif</a>
    </div>

    <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Artikel di arsip tidak tampil di website. Pulihkan untuk mengaktifkannya kembali, atau hapus permanen jika data dan gambar sampulnya tidak lagi diperlukan.
    </p>

    <div class="admin-table-wrap">
        <table class="admin-table divide-y divide-slate-200">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Diarsipkan</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($articles as $article)
                    <tr>
                        <td data-label="Judul" class="px-4 py-3">
                            <span class="font-medium text-slate-800">{{ $article->title }}</span>
                            <span class="block break-all text-xs text-slate-400">{{ $article->slug }}</span>
                        </td>
                        <td data-label="Diarsipkan" class="px-4 py-3 text-slate-600">{{ $article->deleted_at->translatedFormat('d M Y H.i') }}</td>
                        <td data-label="Aksi" class="px-4 py-3">
                            <div class="admin-actions">
                                <form method="POST" action="{{ route('admin.articles.restore', $article) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="admin-action">Pulihkan</button>
                                </form>
                                <form method="POST" action="{{ route('admin.articles.force-delete', $article) }}" data-confirm="Artikel &quot;{{ $article->title }}&quot; dan gambar sampulnya akan dihapus permanen. Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus Permanen Artikel?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-action admin-action-danger">Hapus Permanen</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-12 text-center text-slate-500">Arsip artikel kosong.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $articles->links() }}</div>
@endsection
