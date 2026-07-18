{{--
    Custom delete-confirmation modal that replaces the browser's native confirm().
    Any <form data-confirm="pesan..."> submit is intercepted and routed here.
    Optional: data-confirm-title="Judul", data-confirm-action="Label tombol".
--}}
<div
    x-data="{
        show: false,
        title: 'Konfirmasi Hapus',
        message: 'Yakin ingin menghapus data ini?',
        actionLabel: 'Hapus',
        pendingForm: null,
        openFor(form) {
            this.pendingForm = form;
            this.message = form.getAttribute('data-confirm') || 'Yakin ingin menghapus data ini?';
            this.title = form.getAttribute('data-confirm-title') || 'Konfirmasi Hapus';
            this.actionLabel = form.getAttribute('data-confirm-action') || 'Hapus';
            this.show = true;
            this.$nextTick(() => { this.$refs.confirmBtn && this.$refs.confirmBtn.focus(); });
        },
        accept() {
            const form = this.pendingForm;
            this.show = false;
            this.pendingForm = null;
            if (form) { form.submit(); }
        },
        cancel() { this.show = false; this.pendingForm = null; },
    }"
    x-effect="document.body.classList.toggle('overflow-hidden', show)"
    x-on:submit.capture.window="
        if ($event.target && $event.target.matches && $event.target.matches('form[data-confirm]')) {
            $event.preventDefault();
            openFor($event.target);
        }
    "
    x-on:keydown.escape.window="show && cancel()"
>
    <template x-teleport="body">
        <div x-show="show" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="admin-confirm-title">
            {{-- Backdrop --}}
            <div x-show="show"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-ink-950/60 backdrop-blur-sm" x-on:click="cancel()"></div>

            {{-- Card --}}
            <div x-show="show"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                 class="admin-confirm-card relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex flex-col items-center gap-4 px-6 pb-6 pt-8 text-center sm:px-8 sm:pt-9">
                    <span class="admin-confirm-icon grid h-16 w-16 flex-none place-items-center rounded-full">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </span>

                    <div class="space-y-1.5">
                        <h2 id="admin-confirm-title" class="font-display text-xl font-bold text-slate-800" x-text="title"></h2>
                        <p class="text-sm leading-relaxed text-slate-500" x-text="message"></p>
                    </div>

                    <div class="mt-2 grid w-full grid-cols-2 gap-3">
                        <button type="button" x-on:click="cancel()" class="admin-button admin-button-secondary justify-center">Batal</button>
                        <button type="button" x-ref="confirmBtn" x-on:click="accept()" class="admin-button admin-button-danger-solid justify-center">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span x-text="actionLabel"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
