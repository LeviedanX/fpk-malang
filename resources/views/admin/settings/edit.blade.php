@extends('layouts.admin')

@section('title', 'Pengaturan Website')
@section('heading', 'Pengaturan Website')

@section('content')
    @php
        $defaultLogoUrl = asset('assets/images/branding/logo-fpk.png');
        $faviconFallbackUrl = $settings->logo_path
            ? \Illuminate\Support\Facades\Storage::url($settings->logo_path)
            : $defaultLogoUrl;
    @endphp

    <div
        x-data="{ section: @js(old('settings_section', 'identitas')) }"
        x-init="
            const allowed = ['identitas', 'beranda', 'tentang', 'kontak', 'seo'];
            const hash = window.location.hash.slice(1);
            if (allowed.includes(hash)) section = hash;
            $watch('section', value => history.replaceState(null, '', '#' + value));
        "
        class="space-y-6"
    >
        <div class="rounded-2xl border border-slate-200 bg-white p-2 shadow-sm" aria-label="Bagian Pengaturan Website">
            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-5" role="tablist">
                @foreach ([
                    'identitas' => 'Identitas & Branding',
                    'beranda' => 'Beranda & Hero',
                    'tentang' => 'Tentang FPK',
                    'kontak' => 'Kontak & Media',
                    'seo' => 'SEO',
                ] as $sectionId => $sectionLabel)
                    <button
                        type="button"
                        role="tab"
                        @click="section = '{{ $sectionId }}'"
                        :aria-selected="section === '{{ $sectionId }}'"
                        :class="section === '{{ $sectionId }}'
                            ? 'bg-maroon-700 text-white shadow-sm'
                            : 'bg-slate-50 text-slate-600 hover:bg-maroon-50 hover:text-maroon-800'"
                        class="rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                    >
                        {{ $sectionLabel }}
                    </button>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="settings_section" :value="section">

            <section x-show="section === 'identitas'" x-cloak aria-labelledby="settings-identitas-title">
                <x-admin.card title="Identitas dan Branding" description="Satu sumber untuk identitas yang tampil pada navbar, hero, footer, panel admin, dan metadata.">
                    <div class="space-y-6">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-form.input name="site_name" label="Nama Situs" :value="$settings->site_name" maxlength="60" required hint="Maksimal 60 karakter." />
                            <x-form.input name="organization_name" label="Nama Organisasi" :value="$settings->organization_name" maxlength="100" required hint="Maksimal 100 karakter." />
                            <x-form.input name="abbreviation" label="Singkatan" :value="$settings->abbreviation" maxlength="20" hint="Maksimal 20 karakter." />
                            <x-form.input name="tagline" label="Tagline" :value="$settings->tagline" maxlength="120" hint="Maksimal 120 karakter." />
                        </div>

                        <x-form.textarea name="footer_text" label="Teks Footer" :value="$settings->footer_text" rows="2" maxlength="180" hint="Maksimal 180 karakter." />

                        <div class="grid gap-6 md:grid-cols-3">
                            <x-form.image-field name="logo" label="Logo Organisasi" :current="$settings->logo_path" :fallback="$defaultLogoUrl" />
                            <x-form.image-field name="favicon" label="Favicon" :current="$settings->favicon_path" :fallback="$faviconFallbackUrl" hint="Format PNG atau ICO. Maksimal 512 KB." />
                            <x-form.image-field name="default_og_image" label="Gambar Open Graph" :current="$settings->default_og_image_path" />
                        </div>
                    </div>
                </x-admin.card>
            </section>

            <section x-show="section === 'beranda'" x-cloak aria-labelledby="settings-beranda-title">
                <div class="space-y-6">
                    <x-admin.card title="Beranda dan Hero" description="Gambar, logo, dan teks hero dikelola sebagai elemen terpisah.">
                        <div class="space-y-4">
                            <x-form.input name="hero_title" label="Judul Hero" :value="$profile->hero_title" maxlength="100" required hint="Maksimal 100 karakter." />
                            <x-form.textarea name="hero_subtitle" label="Subtitle Hero" :value="$profile->hero_subtitle" rows="3" maxlength="180" hint="Maksimal 180 karakter." />
                            <x-form.image-field name="hero_image" label="Gambar Hero" :current="$profile->hero_image_path" :fallback="asset('assets/images/branding/hero-card-bg.webp')" hint="Gambar hanya menjadi visual latar. Logo, nama organisasi, dan tagline tetap ditampilkan terpisah." />
                        </div>
                    </x-admin.card>

                    <x-admin.card title="Informasi Lembaga" description="Informasi singkat yang tampil pada panel kredibilitas di beranda.">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-form.input name="institution_legal_basis" label="Dasar Hukum Singkat" :value="$profile->institution_legal_basis" maxlength="120" />
                            <x-form.input name="institution_foundation" label="Landasan Lembaga" :value="$profile->institution_foundation" maxlength="120" />
                        </div>

                        <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Masa Bakti Aktif</p>
                            @if ($activePeriod)
                                <p class="mt-1 font-display text-xl font-bold text-maroon-800">{{ $activePeriod->label() }}</p>
                                <p class="mt-1 text-sm text-slate-600">Bersumber dari periode pengurus yang berstatus aktif.</p>
                            @else
                                <p class="mt-1 text-sm font-medium text-amber-700">Belum ada periode aktif.</p>
                            @endif
                            <a href="{{ route('admin.periods.index') }}" class="admin-button admin-button-secondary mt-3">Kelola Masa Bakti</a>
                        </div>
                    </x-admin.card>
                </div>
            </section>

            <section x-show="section === 'tentang'" x-cloak aria-labelledby="settings-tentang-title">
                <x-admin.card title="Tentang FPK" description="Konten profil organisasi pada halaman utama.">
                    <div class="space-y-4">
                        <x-form.textarea name="definition" label="Pengertian" :value="$profile->definition" rows="4" maxlength="5000" hint="Pisahkan antarparagraf dengan baris kosong." />
                        <x-form.textarea name="background" label="Latar Belakang" :value="$profile->background" rows="6" maxlength="8000" hint="Pisahkan antarparagraf dengan baris kosong." />
                        <x-form.textarea name="objectives" label="Tujuan" :value="$profile->objectives" rows="5" maxlength="5000" hint="Satu poin per baris." />
                        <x-form.textarea name="core_tasks" label="Tugas Pokok" :value="$profile->core_tasks" rows="5" maxlength="5000" hint="Satu poin per baris." />
                        <x-form.textarea name="legal_basis" label="Dasar Hukum" :value="$profile->legal_basis" rows="5" maxlength="5000" hint="Satu poin per baris." />
                        <x-form.image-field name="about_image" label="Ilustrasi Tentang FPK" :current="$profile->about_image_path" :fallback="asset('assets/images/about/about-fpk-vector.webp')" hint="Jika kosong, website menggunakan ilustrasi bawaan." />
                    </div>
                </x-admin.card>
            </section>

            <section x-show="section === 'kontak'" x-cloak aria-labelledby="settings-kontak-title">
                <div class="space-y-6">
                    <x-admin.card title="Kontak" description="Isi hanya informasi resmi yang telah diverifikasi.">
                        <div class="space-y-4">
                            <x-form.textarea name="address" label="Alamat" :value="$contact->address" rows="3" maxlength="500" />
                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-form.input name="phone" label="Telepon" :value="$contact->phone" maxlength="50" />
                                <x-form.input name="whatsapp" label="WhatsApp" :value="$contact->whatsapp" maxlength="50" hint="Contoh: 6281234567890" />
                                <x-form.input name="email" label="Email" type="email" :value="$contact->email" maxlength="255" />
                                <x-form.input name="operational_hours" label="Jam Operasional" :value="$contact->operational_hours" maxlength="255" />
                            </div>
                            <x-form.input name="map_embed_url" label="URL Embed Google Maps" :value="$contact->map_embed_url" maxlength="1000" hint="Gunakan URL embed https://..." />
                        </div>
                    </x-admin.card>

                    <x-admin.card title="Media Sosial">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-form.input name="instagram_url" label="Instagram" :value="$contact->instagram_url" maxlength="255" />
                            <x-form.input name="facebook_url" label="Facebook" :value="$contact->facebook_url" maxlength="255" />
                            <x-form.input name="youtube_url" label="YouTube" :value="$contact->youtube_url" maxlength="255" />
                            <x-form.input name="tiktok_url" label="TikTok" :value="$contact->tiktok_url" maxlength="255" />
                        </div>
                    </x-admin.card>
                </div>
            </section>

            <section x-show="section === 'seo'" x-cloak aria-labelledby="settings-seo-title">
                <x-admin.card title="SEO" description="Metadata default untuk halaman yang tidak memiliki metadata khusus.">
                    <div class="space-y-4">
                        <x-form.input name="default_meta_title" label="Meta Title Default" :value="$settings->default_meta_title" maxlength="70" />
                        <x-form.textarea name="default_meta_description" label="Meta Description Default" :value="$settings->default_meta_description" rows="3" maxlength="160" />
                        <x-form.textarea name="default_meta_keywords" label="Keyword Default" :value="$settings->default_meta_keywords" rows="3" maxlength="500" hint="Pisahkan keyword dengan koma." />
                        <p class="text-sm text-slate-500">Gambar Open Graph dikelola pada bagian Identitas dan Branding agar tidak tersedia dalam dua tempat.</p>
                    </div>
                </x-admin.card>
            </section>

            <div class="sticky bottom-4 z-10 flex justify-stretch rounded-2xl border border-slate-200 bg-white/95 p-3 shadow-xl backdrop-blur sm:justify-end">
                <button type="submit" class="admin-button admin-button-primary w-full sm:w-auto">Simpan Pengaturan Website</button>
            </div>
        </form>
    </div>
@endsection
