@extends('layouts.admin')

@section('title', 'Agenda')
@section('heading', 'Agenda')

@section('content')
    <div class="admin-toolbar">
        <form method="GET" action="{{ route('admin.agendas.index') }}" class="admin-filter">
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari judul..." maxlength="100" class="form-control text-sm sm:min-w-56">
            <select name="status" class="form-control text-sm sm:w-auto">
                <option value="">Semua status</option>
                <option value="published" @selected($status === 'published')>Terbit</option>
                <option value="draft" @selected($status === 'draft')>Draft</option>
            </select>
            <button type="submit" class="admin-button admin-button-dark">Filter</button>
        </form>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.agendas.history') }}" class="admin-button admin-button-secondary">Riwayat Agenda</a>
            <a href="{{ route('admin.agendas.create') }}" class="admin-button admin-button-primary">+ Tambah Agenda</a>
        </div>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table divide-y divide-slate-200">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Waktu Mulai</th>
                    <th class="px-4 py-3">Acara</th>
                    <th class="px-4 py-3">Publikasi</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($agendas as $agenda)
                    @php($effectiveStatus = $agenda->effectiveEventStatus())
                    <tr>
                        <td data-label="Judul" class="px-4 py-3 font-medium text-slate-800">{{ $agenda->title }}</td>
                        <td data-label="Waktu Mulai" class="px-4 py-3 text-slate-600">{{ $agenda->starts_at->translatedFormat('d M Y H.i') }}</td>
                        <td data-label="Acara" class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs {{ $effectiveStatus->badgeClasses() }}">{{ $effectiveStatus->label() }}</span></td>
                        <td data-label="Publikasi" class="px-4 py-3">
                            @if ($agenda->publication_status->value === 'published' && $agenda->published_at?->isFuture())
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-800">Terbit terjadwal</span>
                                <span class="mt-1 block text-xs text-slate-500">{{ $agenda->published_at->translatedFormat('d M Y H.i') }}</span>
                            @else
                                <span class="rounded-full px-2 py-0.5 text-xs {{ $agenda->publication_status->value === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $agenda->publication_status->label() }}</span>
                            @endif
                        </td>
                        <td data-label="Aksi" class="px-4 py-3">
                            <div class="admin-actions">
                                <a href="{{ route('admin.agendas.edit', $agenda) }}" class="admin-action">Ubah</a>
                                <form method="POST" action="{{ route('admin.agendas.destroy', $agenda) }}" data-confirm="Agenda &quot;{{ $agenda->title }}&quot; akan dipindahkan ke arsip dan masih dapat dipulihkan." data-confirm-title="Arsipkan Agenda?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-action admin-action-danger">Arsipkan</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-500">Belum ada agenda aktif. <a href="{{ route('admin.agendas.create') }}" class="text-maroon-700 hover:underline">Tambah agenda pertama</a> atau buka <a href="{{ route('admin.agendas.history') }}" class="text-maroon-700 hover:underline">riwayat agenda</a>.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $agendas->links() }}</div>
@endsection
