@extends('layouts.admin')

@section('title', 'Susunan Pengurus')
@section('heading', 'Susunan Pengurus - Periode')

@section('content')
    <div class="admin-toolbar">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.periods.index') }}" class="admin-button admin-button-primary">Periode</a>
            <a href="{{ route('admin.members.index') }}" class="admin-button admin-button-secondary">Anggota</a>
        </div>
        <a href="{{ route('admin.periods.create') }}" class="admin-button admin-button-primary">+ Tambah Periode</a>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table divide-y divide-slate-200">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Tahun</th>
                    <th class="px-4 py-3">Foto Bersama</th>
                    <th class="px-4 py-3">Anggota</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($periods as $period)
                    <tr>
                        <td data-label="Nama" class="px-4 py-3 font-medium text-slate-800">{{ $period->name }}</td>
                        <td data-label="Tahun" class="px-4 py-3 text-slate-600">{{ $period->label() }}</td>
                        <td data-label="Foto Bersama" class="px-4 py-3">
                            @if ($period->group_photo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($period->group_photo_path) }}"
                                     alt="Foto bersama {{ $period->name }}"
                                     class="h-12 w-20 rounded-lg object-cover ring-1 ring-slate-200">
                            @else
                                <span class="text-xs text-slate-400">Belum diunggah</span>
                            @endif
                        </td>
                        <td data-label="Anggota" class="px-4 py-3">
                            <a href="{{ route('admin.members.index', ['period' => $period->id]) }}" class="font-medium text-maroon-700 hover:underline">{{ $period->members_count }} anggota</a>
                        </td>
                        <td data-label="Status" class="px-4 py-3">
                            @if ($period->is_active)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">Nonaktif</span>
                            @endif
                        </td>
                        <td data-label="Aksi" class="px-4 py-3">
                            <div class="admin-actions">
                                <a href="{{ route('admin.members.index', ['period' => $period->id]) }}" class="admin-action">Anggota</a>
                                <a href="{{ route('admin.periods.edit', $period) }}" class="admin-action">Ubah</a>
                                <form method="POST" action="{{ route('admin.periods.destroy', $period) }}" data-confirm="Menghapus periode ini juga akan menghapus seluruh anggota di dalamnya. Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus Periode?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-action admin-action-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-500">Belum ada periode. <a href="{{ route('admin.periods.create') }}" class="text-maroon-700 hover:underline">Tambah periode</a>.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
