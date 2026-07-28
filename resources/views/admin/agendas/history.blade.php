@extends('layouts.admin')

@section('title', 'Riwayat Agenda')
@section('heading', 'Riwayat Agenda')

@section('content')
    <div class="admin-toolbar">
        <form method="GET" action="{{ route('admin.agendas.history') }}" class="admin-filter">
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari judul riwayat..." maxlength="100" class="form-control text-sm sm:min-w-56">
            <button type="submit" class="admin-button admin-button-dark">Cari</button>
        </form>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.agendas.archive') }}" class="admin-button admin-button-secondary">Arsip Saja</a>
            <a href="{{ route('admin.agendas.index') }}" class="admin-button admin-button-secondary">&larr; Agenda Aktif</a>
        </div>
    </div>

    <p class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
        Agenda yang selesai atau dibatalkan otomatis hilang dari website publik, tetapi tetap tersimpan di sini sebagai dokumentasi. Agenda terarsip ditandai khusus dan dapat dipulihkan.
    </p>

    <div class="admin-table-wrap">
        <table class="admin-table divide-y divide-slate-200">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Waktu Acara</th>
                    <th class="px-4 py-3">Log</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($agendas as $agenda)
                    @php($effectiveStatus = $agenda->effectiveEventStatus())
                    <tr>
                        <td data-label="Judul" class="px-4 py-3">
                            <span class="font-medium text-slate-800">{{ $agenda->title }}</span>
                            <span class="block break-all text-xs text-slate-400">{{ $agenda->slug }}</span>
                        </td>
                        <td data-label="Status" class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs {{ $effectiveStatus->badgeClasses() }}">{{ $effectiveStatus->label() }}</span>
                            @if ($agenda->trashed())
                                <span class="mt-1 block text-xs text-amber-700">Diarsipkan</span>
                            @endif
                        </td>
                        <td data-label="Waktu Acara" class="px-4 py-3 text-slate-600">{{ $agenda->starts_at->translatedFormat('d M Y H.i') }}</td>
                        <td data-label="Log" class="px-4 py-3">
                            <a href="{{ route('admin.agendas.logs', $agenda) }}" class="admin-action">{{ $agenda->logs_count }} aktivitas</a>
                        </td>
                        <td data-label="Aksi" class="px-4 py-3">
                            <div class="admin-actions">
                                @if (! $agenda->trashed())
                                    <a href="{{ route('admin.agendas.edit', $agenda) }}" class="admin-action">Ubah</a>
                                @endif
                                @if ($agenda->trashed())
                                    <form method="POST" action="{{ route('admin.agendas.restore', $agenda) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="admin-action">Pulihkan</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.agendas.force-delete', $agenda) }}" data-confirm="Agenda &quot;{{ $agenda->title }}&quot;, poster, dan seluruh log aktivitasnya akan dihapus permanen. Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus Permanen Agenda?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-action admin-action-danger">Hapus Permanen</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-500">Riwayat agenda masih kosong.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $agendas->links() }}</div>
@endsection
