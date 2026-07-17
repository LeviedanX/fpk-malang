@extends('layouts.admin')

@section('title', 'Anggota Pengurus')
@section('heading', 'Susunan Pengurus - Anggota')

@section('content')
    <div class="admin-toolbar">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.periods.index') }}" class="admin-button admin-button-secondary">Periode</a>
            <a href="{{ route('admin.members.index') }}" class="admin-button admin-button-primary">Anggota</a>
        </div>
        <a href="{{ route('admin.members.create', ['period' => $periodId]) }}" class="admin-button admin-button-primary">+ Tambah Anggota</a>
    </div>

    <form method="GET" action="{{ route('admin.members.index') }}" class="admin-filter">
        <label for="period" class="sr-only">Filter periode</label>
        <select name="period" id="period" onchange="this.form.submit()" class="form-control text-sm sm:w-auto sm:min-w-64">
            <option value="">Semua periode</option>
            @foreach ($periods as $period)
                <option value="{{ $period->id }}" @selected((string) $periodId === (string) $period->id)>{{ $period->name }}</option>
            @endforeach
        </select>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table divide-y divide-slate-200">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">No.</th>
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
                        <td data-label="No." class="px-4 py-3 text-slate-500">{{ $members->firstItem() + $loop->index }}</td>
                        <td data-label="Nama" class="px-4 py-3 font-medium text-slate-800">{{ $member->name }}</td>
                        <td data-label="Jabatan" class="px-4 py-3 text-slate-600">{{ $member->position }}</td>
                        <td data-label="Bidang" class="px-4 py-3 text-slate-600">{{ $member->division ?: '—' }}</td>
                        <td data-label="Status" class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs {{ $member->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $member->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td data-label="Aksi" class="px-4 py-3">
                            <div class="admin-actions">
                                <a href="{{ route('admin.members.edit', $member) }}" class="admin-action">Ubah</a>
                                <form method="POST" action="{{ route('admin.members.destroy', $member) }}" onsubmit="return confirm('Hapus anggota ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-action admin-action-danger">Hapus</button>
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
