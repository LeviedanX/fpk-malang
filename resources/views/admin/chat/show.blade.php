@extends('layouts.admin')

@section('title', 'Percakapan '.$conversation->guest_label)
@section('heading', 'Percakapan '.$conversation->guest_label)

@section('content')
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
        {{-- Transkrip + balasan --}}
        <x-admin.card
            class="flex h-[min(38rem,calc(100dvh-12rem))] flex-col p-0! sm:p-0!"
            data-no-auto-motion
            x-data="adminChatThread({
                endpoints: {
                    poll: '{{ route('admin.chat.poll', $conversation) }}',
                    history: '{{ route('admin.chat.history', $conversation) }}',
                    reply: '{{ route('admin.chat.reply', $conversation) }}',
                },
                {{-- Js::from, bukan @js: direktif Blade tidak dikompilasi di
                     dalam atribut komponen, sehingga @js akan tercetak apa
                     adanya ke HTML. --}}
                seed: {{ \Illuminate\Support\Js::from($messages->map->toWireArray()) }},
                lastId: {{ (int) ($messages->last()->id ?? 0) }},
                hasMore: {{ $hasMore ? 'true' : 'false' }},
                maxImageSize: {{ (int) config('fpk.uploads.max_size') * 1024 }},
                csrf: '{{ csrf_token() }}',
            })"
        >
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
                <div class="min-w-0">
                    <p class="font-display text-base font-semibold text-slate-900">{{ $conversation->guest_label }}</p>
                    <p class="truncate text-xs text-slate-500">
                        {{ $conversation->messages_count }} pesan &middot;
                        terakhir {{ $conversation->last_message_at?->diffForHumans() ?? 'belum ada' }}
                    </p>
                </div>
                <a href="{{ route('admin.chat.index') }}" class="admin-button admin-button-secondary flex-none px-3! py-2!">
                    <span aria-hidden="true">&larr;</span> <span class="hidden sm:inline">Kotak Masuk</span>
                </a>
            </div>

            <div class="admin-chat-log" x-ref="log" x-on:scroll.passive="onScroll()">
                <div class="flex justify-center pb-2" x-show="hasMore">
                    <button type="button" class="chat-more-button" x-on:click="loadOlder()" x-bind:disabled="loadingOlder">
                        <span x-show="!loadingOlder">Muat pesan sebelumnya</span>
                        <span x-show="loadingOlder" x-cloak>Memuat…</span>
                    </button>
                </div>

                {{-- Transkrip awal dikirim lewat prop `seed` di x-data, sehingga
                     halaman tidak perlu satu pun permintaan JavaScript untuk
                     menampilkan isinya dan polling melanjutkan dari sana. --}}
                <template x-for="(message, index) in messages" x-bind:key="message.id">
                    <div>
                        <p class="chat-day" x-show="showDaySeparator(index)" x-text="message.date"></p>
                        <article
                            class="chat-bubble"
                            x-bind:class="{
                                'chat-bubble--admin-side': message.sender === 'admin',
                                'chat-bubble--guest-side': message.sender === 'guest',
                                'is-pending': message.pending,
                                'is-failed': message.failed,
                            }"
                        >
                            <template x-if="message.image">
                                <a x-bind:href="message.image" target="_blank" rel="noopener noreferrer" class="chat-bubble__figure">
                                    <img
                                        x-bind:src="message.image"
                                        x-bind:width="message.image_width"
                                        x-bind:height="message.image_height"
                                        alt="Lampiran gambar"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                </a>
                            </template>
                            <p class="chat-bubble__body" x-show="message.body" x-text="message.body"></p>
                            <p class="chat-bubble__meta">
                                <span x-text="message.sender === 'admin' ? 'Admin' : 'Tamu'"></span>
                                &middot; <span x-text="message.time"></span>
                                <span x-show="message.pending" x-cloak> · mengirim…</span>
                                <span x-show="message.failed" x-cloak> · gagal</span>
                            </p>
                        </article>
                    </div>
                </template>

                <p class="py-10 text-center text-sm text-slate-400" x-show="messages.length === 0" x-cloak>
                    Belum ada pesan pada percakapan ini.
                </p>
            </div>

            <div class="chat-alert" x-show="error" x-cloak role="alert" x-text="error"></div>

            @if ($conversation->is_blocked)
                <p class="border-t border-slate-100 px-4 py-4 text-center text-sm text-rose-600">
                    Percakapan ini diblokir. Cabut blokir untuk melanjutkan balasan.
                </p>
            @else
                <form class="border-t border-slate-100 px-3 py-3" x-on:submit.prevent="send()">
                    <div class="chat-composer__preview" x-show="imagePreview" x-cloak>
                        <img x-bind:src="imagePreview" alt="Pratinjau gambar balasan">
                        <button type="button" class="chat-composer__preview-remove" x-on:click="clearImage()" aria-label="Batalkan gambar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                                <path d="M18 6 6 18M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="chat-composer__row">
                        <label class="chat-icon-button chat-icon-button--muted" x-bind:class="{ 'is-disabled': sending }">
                            <input
                                type="file"
                                id="admin-chat-image-input"
                                name="image"
                                class="sr-only"
                                accept="image/jpeg,image/png,image/webp"
                                x-ref="image"
                                x-on:change="selectImage($event)"
                                x-bind:disabled="sending"
                            >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21.44 11.05 12.25 20.24a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                            </svg>
                            <span class="sr-only">Lampirkan gambar</span>
                        </label>

                        <textarea
                            class="chat-composer__input"
                            id="admin-chat-reply-input"
                            name="body"
                            x-ref="input"
                            x-model="draft"
                            x-on:input="autoGrow()"
                            x-on:keydown.enter.prevent="$event.shiftKey ? insertNewline() : send()"
                            rows="1"
                            maxlength="2000"
                            placeholder="Tulis balasan untuk {{ $conversation->guest_label }}…"
                            aria-label="Tulis balasan"
                            autocomplete="off"
                            x-bind:disabled="sending"
                        ></textarea>

                        <button type="submit" class="chat-send-button" x-bind:disabled="sending || (!draft.trim() && !imageFile)" aria-label="Kirim balasan">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z" />
                            </svg>
                        </button>
                    </div>
                </form>
            @endif
        </x-admin.card>

        {{-- Pelacakan tamu --}}
        <div class="space-y-5">
            <x-admin.card title="Identitas Tamu" description="Tamu tidak mendaftar. Data di bawah adalah sidik jari teknis yang dipakai untuk membedakan satu pengunjung dari yang lain.">
                <dl class="space-y-3 text-sm">
                    @php
                        $facts = [
                            'Nama panggilan' => $conversation->guest_label,
                            'Sidik jari' => $conversation->fingerprint(),
                            'Alamat IP' => $conversation->ip_address ?: 'Tidak tercatat',
                            'Jenis perangkat' => $conversation->deviceTypeLabel(),
                            'Browser' => $conversation->browser_name ?: 'Tidak dikenal',
                            'Sistem operasi' => $conversation->platform_name ?: 'Tidak dikenal',
                            'Mulai percakapan' => $conversation->created_at?->translatedFormat('j F Y, H:i') ?? '—',
                        ];
                    @endphp
                    @foreach ($facts as $label => $value)
                        <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-2 last:border-0 last:pb-0">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</dt>
                            <dd class="text-right font-medium text-slate-700">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                @if ($relatedCount > 0)
                    <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
                        Perangkat ini memiliki {{ $relatedCount }} percakapan lain dengan sidik jari sama.
                        Kemungkinan pengunjung yang sama kembali setelah membersihkan data browser.
                    </p>
                @endif

                <p class="mt-4 text-xs leading-relaxed text-slate-400">
                    Sidik jari dihitung dari IP dan browser, jadi dua orang pada jaringan yang sama bisa terlihat identik.
                    Gunakan sebagai petunjuk, bukan bukti identitas.
                </p>
            </x-admin.card>

            <x-admin.card title="Tindakan">
                <div class="space-y-3">
                    <form method="POST" action="{{ route('admin.chat.status', $conversation) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $conversation->status === \App\Enums\ChatConversationStatus::Open ? 'closed' : 'open' }}">
                        <button type="submit" class="admin-button admin-button-secondary w-full justify-center">
                            {{ $conversation->status === \App\Enums\ChatConversationStatus::Open ? 'Tandai Selesai' : 'Aktifkan Kembali' }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.chat.block', $conversation) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="blocked" value="{{ $conversation->is_blocked ? 0 : 1 }}">
                        <button type="submit" class="admin-button admin-button-secondary w-full justify-center">
                            {{ $conversation->is_blocked ? 'Cabut Blokir' : 'Blokir Tamu Ini' }}
                        </button>
                    </form>

                    <form
                        method="POST"
                        action="{{ route('admin.chat.destroy', $conversation) }}"
                        data-confirm="Percakapan ini beserta seluruh gambar yang dikirim akan dihapus permanen. Lanjutkan?"
                        data-confirm-title="Hapus Percakapan"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-button admin-button-danger w-full justify-center">Hapus Percakapan</button>
                    </form>
                </div>
            </x-admin.card>
        </div>
    </div>
@endsection
