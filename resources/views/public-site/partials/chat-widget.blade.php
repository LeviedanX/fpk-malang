{{-- Widget chat tamu.

     Berjalan tanpa sesi: identitas tamu adalah token acak yang dibuat server
     saat pesan pertama dikirim, lalu disimpan di localStorage dan dikirim
     kembali lewat header X-Chat-Token. Karena itu halaman ini tetap aman
     di-cache CDN—tidak ada cookie maupun token yang ikut tercetak di HTML.

     Seluruh markup di-render dari state Alpine agar tidak ada permintaan
     jaringan sebelum pengunjung benar-benar membuka panel. --}}
<div
    class="chat-widget"
    x-data="guestChat({
        endpoints: {
            show: '{{ route('chat.show') }}',
            poll: '{{ route('chat.poll') }}',
            history: '{{ route('chat.history') }}',
            store: '{{ route('chat.store') }}',
        },
        maxImageSize: {{ (int) config('fpk.uploads.max_size') * 1024 }},
    })"
    x-on:keydown.escape.window="close()"
>
    {{-- Panel --}}
    <section
        class="chat-panel"
        x-ref="panel"
        x-show="open"
        x-cloak
        x-transition:enter="chat-panel-enter"
        x-transition:enter-start="chat-panel-enter-start"
        x-transition:enter-end="chat-panel-enter-end"
        x-transition:leave="chat-panel-leave"
        x-transition:leave-start="chat-panel-enter-end"
        x-transition:leave-end="chat-panel-enter-start"
        role="dialog"
        aria-modal="false"
        aria-labelledby="chat-panel-title"
        x-on:keydown.tab="trapFocus($event)"
    >
        <header class="chat-panel__head">
            <div class="chat-panel__identity">
                <span class="chat-panel__avatar" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="chat-panel__title" id="chat-panel-title">Tanya {{ $site->abbreviation ?: $site->site_name }}</p>
                    <p class="chat-panel__subtitle">
                        <span class="chat-panel__dot" aria-hidden="true"></span>
                        <span x-text="statusText"></span>
                    </p>
                </div>
            </div>
            <button type="button" class="chat-icon-button" x-on:click="close()" aria-label="Tutup obrolan">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>
        </header>

        <div class="chat-log" x-ref="log" x-on:scroll.passive="onScroll()" aria-live="polite" aria-atomic="false">
            <div class="chat-log__top" x-show="hasMore">
                <button type="button" class="chat-more-button" x-on:click="loadOlder()" x-bind:disabled="loadingOlder">
                    <span x-show="!loadingOlder">Muat pesan sebelumnya</span>
                    <span x-show="loadingOlder" x-cloak>Memuat…</span>
                </button>
            </div>

            {{-- Sapaan lokal: tidak disimpan di database sehingga tamu yang
                 hanya membuka panel tidak membuat baris apa pun. --}}
            <div class="chat-bubble chat-bubble--admin chat-bubble--greeting" x-show="messages.length === 0 && !loading" x-cloak>
                <p class="chat-bubble__body">Halo! 👋 Ada yang bisa kami bantu seputar {{ $site->organization_name ?: $site->site_name }}? Silakan tulis pertanyaan Anda, boleh juga melampirkan gambar.</p>
            </div>

            <div class="chat-log__loading" x-show="loading" x-cloak>
                <span class="chat-spinner" aria-hidden="true"></span> Memuat percakapan…
            </div>

            <template x-for="(message, index) in messages" x-bind:key="message.id">
                <div>
                    <p class="chat-day" x-show="showDaySeparator(index)" x-text="message.date"></p>
                    <article
                        class="chat-bubble"
                        x-bind:class="{
                            'chat-bubble--guest': message.sender === 'guest',
                            'chat-bubble--admin': message.sender === 'admin',
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
                            <span x-text="message.time"></span>
                            <span x-show="message.pending" x-cloak> · mengirim…</span>
                            <span x-show="message.failed" x-cloak> · gagal</span>
                        </p>
                    </article>
                </div>
            </template>
        </div>

        <div class="chat-alert" x-show="error" x-cloak role="alert" x-text="error"></div>

        <form class="chat-composer" x-on:submit.prevent="send()">
            <div class="chat-composer__preview" x-show="imagePreview" x-cloak>
                <img x-bind:src="imagePreview" alt="Pratinjau gambar yang akan dikirim">
                <button type="button" class="chat-composer__preview-remove" x-on:click="clearImage()" aria-label="Batalkan gambar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="chat-composer__row">
                <label class="chat-icon-button chat-icon-button--muted" x-bind:class="{ 'is-disabled': sending }">
                    {{-- id + name bukan untuk pengiriman native (FormData
                         disusun manual di send()), melainkan supaya browser
                         mengenali field ini dengan benar. --}}
                    <input
                        type="file"
                        id="chat-image-input"
                        name="image"
                        class="sr-only"
                        accept="{{ collect(config('fpk.uploads.mimes'))->map(fn ($ext) => 'image/'.($ext === 'jpg' ? 'jpeg' : $ext))->unique()->implode(',') }}"
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
                    id="chat-message-input"
                    name="body"
                    x-ref="input"
                    x-model="draft"
                    x-on:input="autoGrow()"
                    x-on:keydown.enter.prevent="$event.shiftKey ? insertNewline() : send()"
                    rows="1"
                    maxlength="2000"
                    placeholder="Tulis pesan…"
                    aria-label="Tulis pesan"
                    autocomplete="off"
                    x-bind:disabled="sending"
                ></textarea>

                <button
                    type="submit"
                    class="chat-send-button"
                    x-bind:disabled="sending || (!draft.trim() && !imageFile)"
                    aria-label="Kirim pesan"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z" />
                    </svg>
                </button>
            </div>
            <p class="chat-composer__hint">Enter untuk kirim · Shift+Enter baris baru</p>
        </form>
    </section>

    {{-- Peluncur --}}
    <button
        type="button"
        class="chat-launcher"
        x-bind:class="{ 'is-open': open }"
        x-on:click="toggle()"
        x-bind:aria-expanded="open"
        aria-controls="chat-panel-title"
        x-bind:aria-label="open ? 'Tutup obrolan' : 'Buka obrolan dengan admin'"
        title="Obrolan dengan admin"
    >
        <svg class="chat-launcher__icon chat-launcher__icon--chat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
        </svg>
        <svg class="chat-launcher__icon chat-launcher__icon--close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
            <path d="M18 6 6 18M6 6l12 12" />
        </svg>
        <span class="chat-launcher__badge" x-show="unread > 0" x-cloak x-text="unread > 9 ? '9+' : unread"></span>
    </button>
</div>
