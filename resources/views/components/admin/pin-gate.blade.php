@props(['pinStatus'])

<div
    class="fixed inset-0 z-[70] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="admin-pin-title"
    x-data="adminPinGate({
        initialSeconds: {{ (int) $pinStatus['seconds'] }},
        setupRequired: {{ $pinStatus['setup_required'] ? 'true' : 'false' }},
    })"
    x-on:keydown.window="handleKey($event)"
>
    <div class="absolute inset-0 bg-ink-950/65 backdrop-blur-md" aria-hidden="true"></div>

    <section class="admin-pin-dialog relative w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
        <div class="px-5 pb-6 pt-7 sm:px-8 sm:pb-8 sm:pt-9">
            <div class="text-center">
                <span class="admin-pin-lock mx-auto" aria-hidden="true">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 10V7a5 5 0 0110 0v3m-11 0h12a2 2 0 012 2v8H4v-8a2 2 0 012-2zM12 14v2"/></svg>
                </span>
                <p class="mt-4 text-[10px] font-bold uppercase tracking-[0.2em] text-maroon-500">Area Terlindungi</p>
                <h2 id="admin-pin-title" class="mt-1 font-display text-2xl font-bold text-slate-900">
                    {{ $pinStatus['setup_required'] ? 'Buat PIN Admin' : 'Masukkan PIN Admin' }}
                </h2>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-slate-500">
                    @if ($pinStatus['setup_required'])
                        Buat PIN 6 digit untuk melindungi Pengaturan Admin. Verifikasi password diperlukan satu kali.
                    @else
                        Pengaturan profil, password, PIN, dan histori aktivitas memerlukan verifikasi tambahan.
                    @endif
                </p>
            </div>

            @if ($errors->has('pin') || $errors->has('current_password'))
                <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-center text-sm font-medium text-rose-700" role="alert">
                    {{ $errors->first('pin') ?: $errors->first('current_password') }}
                </div>
            @endif

            @if ($pinStatus['setup_required'])
                <form method="POST" action="{{ route('admin.account.pin.setup') }}" class="mt-6 space-y-4">
                    @csrf
                    <input type="text" name="username" value="{{ auth()->user()->email }}" autocomplete="username" class="sr-only" tabindex="-1" aria-hidden="true">
                    <x-form.input name="current_password" label="Password Saat Ini" type="password" required autocomplete="current-password" />
                    <x-form.input name="pin" label="PIN Baru" type="password" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="new-password" hint="Gunakan tepat 6 digit angka." />
                    <x-form.input name="pin_confirmation" label="Konfirmasi PIN Baru" type="password" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="new-password" />
                    <button type="submit" class="admin-button admin-button-primary w-full">Buat dan Buka Pengaturan</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.account.unlock') }}" class="mt-6" x-ref="pinForm">
                    @csrf
                    <input type="hidden" name="pin" x-model="pin">

                    <div class="flex justify-center gap-3" aria-label="PIN 6 digit">
                        <template x-for="index in 6" :key="index">
                            <span class="admin-pin-dot" :class="{ 'is-filled': pin.length >= index }"></span>
                        </template>
                    </div>

                    <div x-show="remaining > 0" class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-center" aria-live="polite">
                        <span class="block text-xs font-semibold uppercase tracking-wide text-amber-700">Coba lagi dalam</span>
                        <strong class="mt-0.5 block font-display text-2xl text-amber-900" x-text="formattedTime"></strong>
                    </div>

                    <div class="admin-pin-keypad mt-6" :class="{ 'is-disabled': remaining > 0 }">
                        @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $digit)
                            <button type="button" x-on:click="push('{{ $digit }}')" :disabled="remaining > 0" class="admin-pin-key">{{ $digit }}</button>
                        @endforeach
                        <button type="button" x-on:click="clear()" :disabled="remaining > 0" class="admin-pin-key admin-pin-key-action" aria-label="Hapus semua PIN">C</button>
                        <button type="button" x-on:click="push('0')" :disabled="remaining > 0" class="admin-pin-key">0</button>
                        <button type="button" x-on:click="backspace()" :disabled="remaining > 0" class="admin-pin-key admin-pin-key-action" aria-label="Hapus digit terakhir">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 6H9l-6 6 6 6h12V6zm-8 4 4 4m0-4-4 4"/></svg>
                        </button>
                    </div>

                    <button type="submit" :disabled="pin.length !== 6 || remaining > 0" class="admin-button admin-button-primary mt-5 w-full disabled:cursor-not-allowed disabled:opacity-45">
                        Buka Pengaturan Admin
                    </button>
                </form>
            @endif

            <a href="{{ route('admin.dashboard') }}" class="mt-5 flex items-center justify-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-maroon-700">
                <span aria-hidden="true">&larr;</span> Kembali ke Dashboard
            </a>
        </div>
    </section>
</div>
