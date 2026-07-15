@extends('layouts.admin')

@section('title', 'Anggota Pengurus')
@section('heading', 'Susunan Pengurus - Anggota')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.periods.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Periode</a>
            <a href="{{ route('admin.members.index') }}" class="rounded-md bg-maroon-700 px-4 py-2 text-sm font-medium text-cream-50">Anggota</a>
        </div>
        <a href="{{ route('admin.members.create', ['period' => $periodId]) }}" class="rounded-md bg-maroon-700 px-4 py-2 text-sm font-medium text-cream-50 hover:bg-maroon-800">+ Tambah Anggota</a>
    </div>

    <form method="GET" action="{{ route('admin.members.index') }}" class="flex gap-2">
        <label for="period" class="sr-only">Filter periode</label>
        <select name="period" id="period" onchange="this.form.submit()" class="rounded-md border-slate-300 text-sm shadow-sm focus:border-maroon-600 focus:ring-maroon-600">
            <option value="">Semua periode</option>
            @foreach ($periods as $period)
                <option value="{{ $period->id }}" @selected((string) $periodId === (string) $period->id)>{{ $period->name }}</option>
            @endforeach
        </select>
    </form>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Urutan</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Jabatan</th>
                    <th class="px-4 py-3">Bidang</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($members as $member)
                    <tr>
                        <td class="px-4 py-3 text-slate-500">{{ $member->display_order }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $member->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $member->position }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $member->division ?: '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs {{ $member->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $member->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.members.edit', $member) }}" class="rounded border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">Ubah</a>
                                <form method="POST" action="{{ route('admin.members.destroy', $member) }}" onsubmit="return confirm('Hapus anggota ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded border border-rose-300 px-3 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-500">Belum ada anggota pada filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $members->links() }}</div>
@endsection
