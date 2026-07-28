@extends('layouts.admin')

@section('title', 'Chat Tamu')
@section('heading', 'Chat Tamu')

@section('content')
    <x-admin.card
        title="Kotak Masuk Percakapan"
        description="Pesan dari pengunjung website. Setiap tamu dikenali dari sidik jari perangkat (IP + browser), bukan akun—jadi tidak ada pendaftaran yang perlu mereka lakukan."
    >
        @php
            $filters = [
                'all' => ['Semua', $counts['all']],
                'unread' => ['Belum dibaca', $counts['unread']],
                'open' => ['Aktif', $counts['open']],
                'closed' => ['Selesai', $counts['closed']],
            ];
        @endphp

        <div class="mb-5 flex flex-wrap gap-2" role="tablist" aria-label="Saring percakapan">
            @foreach ($filters as $key => [$label, $count])
                <a
                    href="{{ route('admin.chat.index', $key === 'all' ? [] : ['filter' => $key]) }}"
                    @class([
                        'inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-xs font-semibold transition',
                        'border-maroon-700 bg-maroon-700 text-white' => $filter === $key,
                        'border-slate-200 bg-white text-slate-600 hover:border-maroon-300 hover:text-maroon-700' => $filter !== $key,
                    ])
                    @if ($filter === $key) aria-current="page" @endif
                >
                    {{ $label }}
                    <span @class([
                        'rounded-full px-1.5 py-0.5 text-[10px] leading-none',
                        'bg-white/20' => $filter === $key,
                        'bg-slate-100 text-slate-500' => $filter !== $key,
                    ])>{{ $count }}</span>
                </a>
            @endforeach
        </div>

        @if ($conversations->isEmpty())
            <div class="py-12 text-center">
                <p class="font-display text-xl font-semibold text-slate-800">Belum ada percakapan</p>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                    Percakapan muncul di sini segera setelah pengunjung mengirim pesan pertama lewat tombol chat di halaman publik.
                </p>
            </div>
        @else
            <ul class="divide-y divide-slate-100" data-no-auto-motion>
                @foreach ($conversations as $conversation)
                    <li>
                        <a
                            href="{{ route('admin.chat.show', $conversation) }}"
                            @class([
                                'flex gap-3 rounded-xl px-2 py-3.5 transition hover:bg-slate-50 sm:px-3',
                                'bg-amber-50/60' => $conversation->admin_unread_count > 0,
                            ])
                        >
                            <span @class([
                                'grid h-10 w-10 flex-none place-items-center rounded-full text-xs font-bold',
                                'bg-maroon-700 text-cream-50' => $conversation->admin_unread_count > 0,
                                'bg-slate-100 text-slate-500' => $conversation->admin_unread_count === 0,
                            ])>{{ mb_substr($conversation->guest_label, -4) }}</span>

                            <span class="min-w-0 flex-1">
                                <span class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <span class="font-semibold text-slate-800">{{ $conversation->guest_label }}</span>

                                    @if ($conversation->admin_unread_count > 0)
                                        <span class="rounded-full bg-maroon-700 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">
                                            {{ $conversation->admin_unread_count }} baru
                                        </span>
                                    @endif

                                    @if ($conversation->is_blocked)
                                        <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700">Diblokir</span>
                                    @elseif ($conversation->status === \App\Enums\ChatConversationStatus::Closed)
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500">Selesai</span>
                                    @endif
                                </span>

                                <span class="mt-0.5 block truncate text-sm text-slate-500">{{ $conversation->preview() }}</span>

                                <span class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-slate-400">
                                    <span title="Jenis perangkat">{{ $conversation->deviceTypeLabel() }}</span>
                                    <span aria-hidden="true">&middot;</span>
                                    <span title="Browser dan sistem operasi">{{ $conversation->deviceLabel() }}</span>
                                    <span aria-hidden="true">&middot;</span>
                                    <span title="Alamat IP">{{ $conversation->ip_address ?: 'IP tidak diketahui' }}</span>
                                </span>
                            </span>

                            <span class="flex-none self-start text-right text-[11px] text-slate-400">
                                {{ $conversation->last_message_at?->diffForHumans(short: true) ?? '—' }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-5">
                {{ $conversations->links() }}
            </div>
        @endif
    </x-admin.card>
@endsection
