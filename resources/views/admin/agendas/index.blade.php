@extends('layouts.admin')

@section('title', 'Agenda')
@section('heading', 'Agenda')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.agendas.index') }}" class="flex flex-wrap gap-2">
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari judul..."
                class="rounded-md border-slate-300 text-sm shadow-sm focus:border-maroon-600 focus:ring-maroon-600">
            <select name="status" class="rounded-md border-slate-300 text-sm shadow-sm focus:border-maroon-600 focus:ring-maroon-600">
                <option value="">Semua status</option>
                <option value="published" @selected($status === 'published')>Terbit</option>
                <option value="draft" @selected($status === 'draft')>Draft</option>
            </select>
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Filter</button>
        </form>

        <a href="{{ route('admin.agendas.create') }}" class="rounded-md bg-maroon-700 px-4 py-2 text-sm font-medium text-cream-50 hover:bg-maroon-800">+ Tambah Agenda</a>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
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
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $agenda->title }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $agenda->starts_at->translatedFormat('d M Y H.i') }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs {{ $agenda->event_status->badgeClasses() }}">{{ $agenda->event_status->label() }}</span></td>
                        <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs {{ $agenda->publication_status->value === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $agenda->publication_status->label() }}</span></td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.agendas.edit', $agenda) }}" class="rounded border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">Ubah</a>
                                <form method="POST" action="{{ route('admin.agendas.destroy', $agenda) }}" onsubmit="return confirm('Hapus agenda ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded border border-rose-300 px-3 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-500">Belum ada agenda. <a href="{{ route('admin.agendas.create') }}" class="text-maroon-700 hover:underline">Tambah agenda pertama</a>.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $agendas->links() }}</div>
@endsection
