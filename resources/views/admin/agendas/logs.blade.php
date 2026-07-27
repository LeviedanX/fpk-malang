@extends('layouts.admin')

@section('title', 'Log Agenda')
@section('heading', 'Log Agenda')

@section('content')
    <div class="admin-toolbar">
        <div>
            <p class="text-sm text-slate-500">Riwayat aktivitas untuk</p>
            <h2 class="font-display text-xl font-bold text-slate-800">{{ $agenda->title }}</h2>
        </div>
        <a href="{{ route('admin.agendas.history') }}" class="admin-button admin-button-secondary">&larr; Riwayat Agenda</a>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table divide-y divide-slate-200">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Waktu</th>
                    <th class="px-4 py-3">Aktivitas</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Admin</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($logs as $log)
                    @php($actionLabel = match ($log->action) {
                        'created' => 'Agenda dibuat',
                        'updated' => 'Agenda diperbarui',
                        'archived' => 'Agenda diarsipkan',
                        'restored' => 'Agenda dipulihkan',
                        default => ucfirst($log->action),
                    })
                    <tr>
                        <td data-label="Waktu" class="px-4 py-3 text-slate-600">{{ $log->created_at->translatedFormat('d M Y H.i') }}</td>
                        <td data-label="Aktivitas" class="px-4 py-3">
                            <span class="font-medium text-slate-800">{{ $actionLabel }}</span>
                            @if ($log->changes)
                                <span class="mt-1 block text-xs text-slate-500">Field: {{ implode(', ', $log->changes) }}</span>
                            @endif
                        </td>
                        <td data-label="Status" class="px-4 py-3 text-sm text-slate-600">
                            {{ $log->status_from ? (\App\Enums\AgendaStatus::tryFrom($log->status_from)?->label() ?? $log->status_from) : '—' }}
                            @if ($log->status_to)
                                <span aria-hidden="true">&rarr;</span>
                                {{ \App\Enums\AgendaStatus::tryFrom($log->status_to)?->label() ?? $log->status_to }}
                            @endif
                        </td>
                        <td data-label="Admin" class="px-4 py-3 text-slate-600">{{ $log->user?->name ?? 'Sistem' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center text-slate-500">Belum ada aktivitas yang tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
