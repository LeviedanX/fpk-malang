@extends('layouts.admin')

@section('title', 'Pengaturan Admin')
@section('heading', 'Pengaturan Admin')

@section('content')
    @if (! $isUnlocked)
        <div class="admin-locked-placeholder" aria-hidden="true">
            <div class="h-28 rounded-2xl bg-white/55"></div>
            <div class="grid gap-5 lg:grid-cols-2">
                <div class="h-72 rounded-2xl bg-white/45"></div>
                <div class="h-72 rounded-2xl bg-white/45"></div>
            </div>
        </div>
        <x-admin.pin-gate :pin-status="$pinStatus" />
    @else
        <section class="admin-security-overview">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <span class="grid h-12 w-12 flex-none place-items-center rounded-xl bg-emerald-100 text-emerald-700" aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.6-5.4A9 9 0 1112 3a9 9 0 018.6 3.6z"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-700">Pengaturan Terbuka</p>
                        <h2 class="mt-1 font-display text-xl font-bold text-slate-900">Keamanan Administrator</h2>
                        <p class="mt-1 text-sm leading-relaxed text-slate-500">Akses telah diverifikasi menggunakan PIN. Perubahan sensitif tetap memerlukan kredensial saat ini.</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-2 self-start rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 sm:self-center">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Sesi tindakan 15 menit
                </span>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-admin.card title="Profil Administrator" description="Ubah nama dan email yang digunakan untuk mengelola website.">
                <form method="POST" action="{{ route('admin.account.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <x-form.input name="name" label="Nama" :value="$user->name" required />
                    <x-form.input name="email" label="Email" type="email" :value="$user->email" required autocomplete="username" />
                    <x-form.input name="current_password" label="Password Saat Ini" type="password" required autocomplete="current-password" hint="Diperlukan untuk mencegah perubahan identitas oleh sesi yang tidak sah." />
                    <div class="flex justify-stretch sm:justify-end">
                        <button type="submit" class="admin-button admin-button-primary w-full sm:w-auto">Simpan Profil</button>
                    </div>
                </form>
            </x-admin.card>

            <x-admin.card title="Ubah Password" description="Gunakan password kuat dan berbeda dari layanan lain.">
                <form method="POST" action="{{ route('admin.account.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="text" name="username" value="{{ $user->email }}" autocomplete="username" class="sr-only" tabindex="-1" aria-hidden="true">
                    <x-form.input name="current_password" label="Password Saat Ini" type="password" required autocomplete="current-password" />
                    <x-form.input name="password" label="Password Baru" type="password" required autocomplete="new-password" />
                    <x-form.input name="password_confirmation" label="Konfirmasi Password Baru" type="password" required autocomplete="new-password" />
                    <div class="flex justify-stretch sm:justify-end">
                        <button type="submit" class="admin-button admin-button-primary w-full sm:w-auto">Perbarui Password</button>
                    </div>
                </form>
            </x-admin.card>
        </div>

        <x-admin.card title="Ubah PIN Pengaturan" description="PIN harus terdiri dari tepat 6 digit angka dan berbeda dari PIN saat ini.">
            <form method="POST" action="{{ route('admin.account.pin.update') }}" class="grid gap-4 md:grid-cols-3">
                @csrf
                @method('PUT')
                <input type="text" name="username" value="{{ $user->email }}" autocomplete="username" class="sr-only" tabindex="-1" aria-hidden="true">
                <x-form.input name="current_pin" label="PIN Saat Ini" type="password" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="current-password" />
                <x-form.input name="pin" label="PIN Baru" type="password" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="new-password" />
                <x-form.input name="pin_confirmation" label="Konfirmasi PIN Baru" type="password" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="new-password" />
                <div class="md:col-span-3 flex justify-stretch sm:justify-end">
                    <button type="submit" class="admin-button admin-button-primary w-full sm:w-auto">Perbarui PIN</button>
                </div>
            </form>
        </x-admin.card>

        <x-admin.card title="Histori Aktivitas Admin" description="Rekaman akses dan perubahan pada seluruh fitur administrator.">
            <div class="mb-5 flex flex-col gap-3 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">Menampilkan aktivitas terbaru beserta waktu, IP, perangkat, dan status respons.</p>
                <form
                    method="POST"
                    action="{{ route('admin.account.logs.clear') }}"
                    data-confirm="Seluruh histori aktivitas admin akan dihapus permanen. Tindakan ini tidak dapat dibatalkan."
                    data-confirm-title="Hapus Seluruh Histori?"
                    data-confirm-action="Hapus Histori"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="admin-button admin-button-danger w-full sm:w-auto" @disabled($activityLogs->isEmpty())>
                        Hapus Seluruh Histori
                    </button>
                </form>
            </div>

            <div class="admin-table-wrap shadow-none!">
                <table class="admin-table divide-y divide-slate-200">
                    <thead class="text-left">
                        <tr>
                            <th class="px-4 py-3">Aktivitas</th>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">IP / Perangkat</th>
                            <th class="px-4 py-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($activityLogs as $log)
                            <tr>
                                <td data-label="Aktivitas" class="px-4 py-3">
                                    <span class="block font-medium text-slate-800">{{ $log->description }}</span>
                                    <span class="mt-0.5 block break-all text-xs text-slate-400">{{ $log->method }} &middot; {{ $log->route_name ?: $log->path }}</span>
                                </td>
                                <td data-label="Waktu" class="px-4 py-3 text-slate-600">
                                    {{ $log->created_at->translatedFormat('d M Y, H.i.s') }}
                                </td>
                                <td data-label="IP / Perangkat" class="px-4 py-3 text-slate-600">
                                    <span class="block">{{ $log->ip_address ?: 'Tidak tersedia' }}</span>
                                    <span class="block font-mono text-[11px] text-slate-400">{{ $log->device_key ? \Illuminate\Support\Str::limit($log->device_key, 12, '') : 'tanpa-id' }}</span>
                                </td>
                                <td data-label="Status" class="px-4 py-3 text-right">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                        'bg-emerald-100 text-emerald-700' => ($log->status_code ?? 500) < 400,
                                        'bg-rose-100 text-rose-700' => ($log->status_code ?? 500) >= 400,
                                    ])>{{ $log->status_code ?? '—' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-slate-500">Belum ada histori aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($activityLogs->hasPages())
                <div class="mt-5">{{ $activityLogs->links() }}</div>
            @endif
        </x-admin.card>
    @endif
@endsection
