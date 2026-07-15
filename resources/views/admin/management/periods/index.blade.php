@extends('layouts.admin')

@section('title', 'Susunan Pengurus')
@section('heading', 'Susunan Pengurus - Periode')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.periods.index') }}" class="rounded-md bg-maroon-700 px-4 py-2 text-sm font-medium text-cream-50">Periode</a>
            <a href="{{ route('admin.members.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Anggota</a>
        </div>
        <a href="{{ route('admin.periods.create') }}" class="rounded-md bg-maroon-700 px-4 py-2 text-sm font-medium text-cream-50 hover:bg-maroon-800">+ Tambah Periode</a>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Tahun</th>
                    <th class="px-4 py-3">Anggota</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($periods as $period)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $period->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $period->label() }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.members.index', ['period' => $period->id]) }}" class="text-maroon-700 hover:underline">{{ $period->members_count }} anggota</a>
                        </td>
                        <td class="px-4 py-3">
                            @if ($period->is_active)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.members.index', ['period' => $period->id]) }}" class="rounded border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">Anggota</a>
                                <a href="{{ route('admin.periods.edit', $period) }}" class="rounded border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">Ubah</a>
                                <form method="POST" action="{{ route('admin.periods.destroy', $period) }}" onsubmit="return confirm('Hapus periode ini beserta seluruh anggotanya?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded border border-rose-300 px-3 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-500">Belum ada periode. <a href="{{ route('admin.periods.create') }}" class="text-maroon-700 hover:underline">Tambah periode</a>.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
