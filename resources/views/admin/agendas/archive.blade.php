@extends('layouts.admin')

@section('title', 'Arsip Agenda')
@section('heading', 'Arsip Agenda')

@section('content')
    <div class="admin-toolbar">
        <form method="GET" action="{{ route('admin.agendas.archive') }}" class="admin-filter">
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari judul arsip..." class="form-control text-sm sm:min-w-56">
            <button type="submit" class="admin-button admin-button-dark">Cari</button>
        </form>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.agendas.history') }}" class="admin-button admin-button-secondary">Riwayat Agenda</a>
            <a href="{{ route('admin.agendas.index') }}" class="admin-button admin-button-secondary">&larr; Agenda Aktif</a>
        </div>
    </div>

    <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Agenda di arsip tidak tampil di website. Pulihkan untuk mengaktifkannya kembali, atau hapus permanen jika data dan posternya tidak lagi diperlukan.
    </p>

    <div class="admin-table-wrap">
        <table class="admin-table divide-y divide-slate-200">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Waktu Acara</th>
                    <th class="px-4 py-3">Diarsipkan</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($agendas as $agenda)
                    <tr>
                        <td data-label="Judul" class="px-4 py-3">
                            <span class="font-medium text-slate-800">{{ $agenda->title }}</span>
                            <span class="block break-all text-xs text-slate-400">{{ $agenda->slug }}</span>
                        </td>
                        <td data-label="Waktu Acara" class="px-4 py-3 text-slate-600">{{ $agenda->starts_at->translatedFormat('d M Y H.i') }}</td>
                        <td data-label="Diarsipkan" class="px-4 py-3 text-slate-600">{{ $agenda->deleted_at->translatedFormat('d M Y H.i') }}</td>
                        <td data-label="Aksi" class="px-4 py-3">
                            <div class="admin-actions">
                                <form method="POST" action="{{ route('admin.agendas.restore', $agenda) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="admin-action">Pulihkan</button>
                                </form>
                                <form method="POST" action="{{ route('admin.agendas.force-delete', $agenda) }}" data-confirm="Agenda &quot;{{ $agenda->title }}&quot; dan posternya akan dihapus permanen. Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus Permanen Agenda?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-action admin-action-danger">Hapus Permanen</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center text-slate-500">Arsip agenda kosong.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $agendas->links() }}</div>
@endsection
