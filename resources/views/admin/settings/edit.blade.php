@extends('layouts.admin')

@section('title', 'Pengaturan Website')
@section('heading', 'Pengaturan Website')

@section('content')
    @php
        $defaultLogoUrl = asset('assets/images/branding/logo-fpk.webp');
        $faviconFallbackUrl = $settings->logo_path
            ? \Illuminate\Support\Facades\Storage::url($settings->logo_path)
            : $defaultLogoUrl;
        $heroDesktopBackgroundUrl = $profile->hero_background_path
            ? \Illuminate\Support\Facades\Storage::url($profile->hero_background_path)
            : null;
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
                    'beranda' => 'Hero Beranda',
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
                <x-admin.card title="Identitas dan Branding" description="Satu sumber untuk identitas yang tampil pada navbar, footer, panel admin, dan metadata.">
                    <div class="space-y-6">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-form.input
                                name="site_name"
                                label="Nama Situs"
                                :value="$settings->site_name"
                                maxlength="60"
                                required
                                hint="Digunakan sebagai identitas teknis website, nama pada panel admin dan halaman login, Open Graph, structured data, serta sebagai cadangan jika Judul SEO Default belum diisi. Navbar dan footer menggunakan kolom Singkatan apabila tersedia. Maksimal 60 karakter."
                            />
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

                <x-admin.card title="Tampilan Login Administrator" description="Atur gambar latar halaman login tanpa mengubah tampilan website publik.">
                    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,28rem)_minmax(0,1fr)]">
                        <div class="space-y-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:p-5">
                            <x-form.image-field
                                name="admin_login_background"
                                label="Background Halaman Login"
                                :current="$settings->admin_login_background_path"
                                hint="Disarankan rasio 16:9, misalnya 1920×1080. Format JPG, PNG, atau WEBP; maksimal 2 MB."
                            />

                            @if ($settings->admin_login_background_path)
                                <label class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                                    <input
                                        type="checkbox"
                                        name="remove_admin_login_background"
                                        value="1"
                                        @checked(old('remove_admin_login_background'))
                                        class="mt-0.5 rounded border-rose-300 text-rose-700 focus:ring-rose-600"
                                    >
                                    <span>
                                        <span class="block font-semibold">Hapus background login</span>
                                        <span class="mt-0.5 block text-xs text-rose-700">Halaman login kembali memakai latar marun bawaan.</span>
                                    </span>
                                </label>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-maroon-100 bg-maroon-50/55 p-5">
                            <p class="text-sm font-semibold text-maroon-900">Tampilan tetap terbaca</p>
                            <p class="mt-2 max-w-xl text-sm leading-relaxed text-slate-600">
                                Sistem otomatis menambahkan lapisan marun gelap di atas gambar agar kartu login tetap fokus dan kontras. Gunakan gambar yang tenang, tidak terlalu ramai, dan tidak memuat teks penting.
                            </p>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Musik Latar" description="Atur lagu, tampilan tombol, status awal, dan volume untuk halaman publik.">
                    <div
                        x-data="{ volume: Number(@js(old('background_music_volume', $settings->background_music_volume ?? 50))) }"
                        class="space-y-5"
                    >
                        @if ($settings->background_music_path)
                            <div class="space-y-2">
                                <p class="text-sm font-semibold text-slate-700">Lagu saat ini</p>
                                <audio
                                    controls
                                    preload="none"
                                    x-init="$el.volume = volume / 100"
                                    class="w-full max-w-md"
                                    src="{{ \Illuminate\Support\Facades\Storage::url($settings->background_music_path) }}"
                                ></audio>
                            </div>
                        @endif

                        <div class="space-y-2">
                            <label for="background_music" class="block text-sm font-semibold text-slate-700">
                                {{ $settings->background_music_path ? 'Ganti lagu' : 'Unggah lagu' }}
                            </label>
                            <input
                                type="file"
                                name="background_music"
                                id="background_music"
                                accept="audio/mpeg,audio/wav,audio/ogg,audio/mp4,audio/x-m4a,.mp3,.wav,.ogg,.m4a"
                                aria-describedby="background_music-hint"
                                class="block w-full max-w-md cursor-pointer rounded-xl border border-dashed border-slate-300 bg-slate-50 p-2 text-sm text-slate-600 transition hover:border-maroon-300 hover:bg-maroon-50/50 file:mr-3 file:rounded-lg file:border-0 file:bg-maroon-700 file:px-3 file:py-2 file:text-sm file:font-medium file:text-cream-50 hover:file:bg-maroon-800"
                            >
                            <p id="background_music-hint" class="text-xs text-slate-500">Format MP3, WAV, OGG, atau M4A. Maksimal 10 MB.</p>

                            @error('background_music')
                                <p class="text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <input type="hidden" name="background_music_visible" value="0">
                                <label class="flex items-start gap-3 text-sm text-slate-700">
                                    <input
                                        type="checkbox"
                                        name="background_music_visible"
                                        value="1"
                                        @checked(old('background_music_visible', $settings->background_music_visible ?? true))
                                        class="mt-0.5 rounded border-slate-300 text-maroon-700 focus:ring-maroon-600"
                                    >
                                    <span>
                                        <span class="block font-semibold">Tampilkan fitur musik</span>
                                        <span class="mt-0.5 block text-xs text-slate-500">Jika dinonaktifkan, audio dan tombol musik disembunyikan dari seluruh halaman publik.</span>
                                    </span>
                                </label>
                            </div>

                            <fieldset class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <legend class="px-1 text-sm font-semibold text-slate-700">Status awal musik</legend>
                                <div class="mt-2 flex flex-wrap gap-4">
                                    @foreach (['1' => 'On', '0' => 'Off'] as $value => $label)
                                        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                                            <input
                                                type="radio"
                                                name="background_music_default_playing"
                                                value="{{ $value }}"
                                                @checked((int) old('background_music_default_playing', (int) ($settings->background_music_default_playing ?? true)) === $value)
                                                class="border-slate-300 text-maroon-700 focus:ring-maroon-600"
                                                required
                                            >
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                                <p class="mt-2 text-xs text-slate-500">Default baru adalah On. Saat konfigurasi musik diubah dan disimpan, preferensi lama pengunjung direset mengikuti pilihan admin.</p>
                                <p class="mt-1 text-xs text-amber-700">Browser dapat menunda suara sampai klik/tap pertama, tetapi status musik tetap On.</p>
                            </fieldset>
                        </div>

                        <div class="max-w-xl rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <label for="background_music_volume" class="text-sm font-semibold text-slate-700">Volume musik</label>
                                <output
                                    for="background_music_volume"
                                    x-text="`${volume}%`"
                                    class="min-w-14 rounded-lg bg-white px-2 py-1 text-center text-sm font-bold text-maroon-700 ring-1 ring-slate-200"
                                >{{ old('background_music_volume', $settings->background_music_volume ?? 50) }}%</output>
                            </div>
                            <input
                                type="range"
                                name="background_music_volume"
                                id="background_music_volume"
                                min="0"
                                max="100"
                                step="1"
                                x-model.number="volume"
                                @input="$root.querySelector('audio') && ($root.querySelector('audio').volume = volume / 100)"
                                class="mt-3 w-full accent-maroon-700"
                            >
                            <div class="mt-1 flex justify-between text-xs text-slate-500" aria-hidden="true">
                                <span>0%</span>
                                <span>100%</span>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">Volume ini berlaku untuk lagu di halaman publik dan preview lagu saat ini.</p>
                        </div>

                        @if ($settings->background_music_path)
                            <label class="flex max-w-md items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                                <input
                                    type="checkbox"
                                    name="remove_background_music"
                                    value="1"
                                    @checked(old('remove_background_music'))
                                    class="mt-0.5 rounded border-rose-300 text-rose-700 focus:ring-rose-600"
                                >
                                <span>
                                    <span class="block font-semibold">Hapus musik latar</span>
                                    <span class="mt-0.5 block text-xs text-rose-700">Tombol musik di navbar akan hilang sampai lagu baru diunggah.</span>
                                </span>
                            </label>
                        @endif
                    </div>
                </x-admin.card>
            </section>

            <section x-show="section === 'beranda'" x-cloak aria-labelledby="settings-beranda-title">
                <div class="space-y-6">
                    <x-admin.card title="Konten Utama Hero" description="Seluruh teks utama pada bagian paling atas Beranda dapat disesuaikan di sini.">
                        <div class="space-y-4">
                            <x-form.input name="hero_eyebrow" label="Teks Pembuka" :value="$profile->hero_eyebrow ?: $settings->organization_name" maxlength="120" required hint="Teks kecil berwarna emas di atas judul. Maksimal 120 karakter." />
                            <x-form.input name="hero_title" label="Judul Hero" :value="$profile->hero_title" maxlength="100" required hint="Maksimal 100 karakter." />
                            <x-form.textarea name="hero_subtitle" label="Subtitle Hero" :value="$profile->hero_subtitle" rows="3" maxlength="180" hint="Kosongkan jika subtitle tidak perlu ditampilkan. Maksimal 180 karakter." />

                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-form.input name="hero_primary_cta_label" label="Label Tombol Utama" :value="$profile->hero_primary_cta_label ?: 'Tentang FPK'" maxlength="60" required hint="Tombol tetap menuju bagian Tentang FPK." />
                                <x-form.input name="hero_secondary_cta_label" label="Label Tombol Agenda" :value="$profile->hero_secondary_cta_label ?: 'Lihat Agenda'" maxlength="60" required hint="Tampil saat ada agenda publik dan tetap menuju bagian Agenda." />
                            </div>
                        </div>
                    </x-admin.card>

                    <x-admin.card title="Tampilan Hero Responsif" description="Gunakan gambar terpisah agar komposisi background tetap pas pada layar desktop dan Android/mobile.">
                        <div class="grid gap-6 lg:grid-cols-2">
                            <div class="space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <x-form.image-field
                                    name="hero_background"
                                    label="Background Desktop"
                                    :current="$profile->hero_background_path"
                                    hint="Disarankan rasio 16:9, misalnya 1920×1080. Format JPG, PNG, atau WEBP; maksimal 2 MB."
                                />

                                @if ($profile->hero_background_path)
                                    <label class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                                        <input
                                            type="checkbox"
                                            name="remove_hero_background"
                                            value="1"
                                            @checked(old('remove_hero_background'))
                                            class="mt-0.5 rounded border-rose-300 text-rose-700 focus:ring-rose-600"
                                        >
                                        <span>
                                            <span class="block font-semibold">Hapus background desktop</span>
                                            <span class="mt-0.5 block text-xs text-rose-700">Desktop kembali menggunakan background bawaan.</span>
                                        </span>
                                    </label>
                                @endif
                            </div>

                            <div class="space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <x-form.image-field
                                    name="hero_mobile_background"
                                    label="Background Android / Mobile"
                                    :current="$profile->hero_mobile_background_path"
                                    :fallback="$heroDesktopBackgroundUrl"
                                    hint="Disarankan rasio potret 9:16, misalnya 1080×1920. Jika kosong, sistem otomatis memakai background desktop."
                                />

                                @if ($profile->hero_mobile_background_path)
                                    <label class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                                        <input
                                            type="checkbox"
                                            name="remove_hero_mobile_background"
                                            value="1"
                                            @checked(old('remove_hero_mobile_background'))
                                            class="mt-0.5 rounded border-rose-300 text-rose-700 focus:ring-rose-600"
                                        >
                                        <span>
                                            <span class="block font-semibold">Hapus background mobile</span>
                                            <span class="mt-0.5 block text-xs text-rose-700">Mobile akan kembali memakai background desktop sebagai fallback.</span>
                                        </span>
                                    </label>
                                @endif
                            </div>
                        </div>

                        <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-relaxed text-amber-800">
                            Kedua gambar menggunakan mode <strong>cover</strong> dan fokus tengah. Lapisan gelap otomatis menjaga teks tetap terbaca pada berbagai ukuran layar.
                        </p>
                    </x-admin.card>

                    <x-admin.card title="Panel Informasi Hero" description="Edit label dan isi panel kredibilitas yang tampil di bawah tombol hero.">
                        <div class="grid gap-5 xl:grid-cols-3">
                            <div class="space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <x-form.input name="hero_legal_basis_label" label="Label Dasar Hukum" :value="$profile->hero_legal_basis_label ?: 'Dasar Hukum'" maxlength="60" required />
                                <x-form.input name="institution_legal_basis" label="Isi Dasar Hukum" :value="$profile->institution_legal_basis" maxlength="120" hint="Kosongkan untuk menyembunyikan item ini." />
                            </div>

                            <div class="space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <x-form.input name="hero_foundation_label" label="Label Landasan" :value="$profile->hero_foundation_label ?: 'Landasan'" maxlength="60" required />
                                <x-form.input name="institution_foundation" label="Isi Landasan" :value="$profile->institution_foundation" maxlength="120" hint="Kosongkan untuk menyembunyikan item ini." />
                            </div>

                            <div class="space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <x-form.input name="hero_period_label" label="Label Masa Bakti" :value="$profile->hero_period_label ?: 'Masa Bakti'" maxlength="60" required />
                                <div>
                                    <p class="text-sm font-medium text-slate-700">Isi Masa Bakti Aktif</p>
                                    @if ($activePeriod)
                                        <p class="mt-1 font-display text-xl font-bold text-maroon-800">{{ $activePeriod->label() }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Nilai mengikuti periode pengurus yang berstatus aktif.</p>
                                    @else
                                        <p class="mt-1 text-sm font-medium text-amber-700">Belum ada periode aktif.</p>
                                    @endif
                                    <a href="{{ route('admin.periods.index') }}" class="admin-button admin-button-secondary mt-3">Kelola Masa Bakti</a>
                                </div>
                            </div>
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
                            <x-form.input name="map_embed_url" label="URL Embed Google Maps" :value="$contact->map_embed_url" maxlength="1000" hint="Tempel URL saja (bukan kode iframe), misalnya https://www.google.com/maps/embed?pb=..." />
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
                <x-admin.card title="SEO" description="Metadata utama untuk membantu mesin pencari dan pratinjau tautan memahami website. Halaman artikel tetap memakai metadata khususnya sendiri.">
                    <div class="space-y-4">
                        <x-form.input
                            name="default_meta_title"
                            label="Judul SEO Default"
                            :value="$settings->default_meta_title"
                            maxlength="60"
                            hint="Target 50–60 karakter. Gunakan nama organisasi dan wilayah; hindari pengulangan keyword."
                        />
                        <x-form.textarea
                            name="default_meta_description"
                            label="Deskripsi SEO Default"
                            :value="$settings->default_meta_description"
                            rows="3"
                            maxlength="160"
                            hint="Target 140–160 karakter. Jelaskan identitas, layanan informasi, dan cakupan Kota Malang secara natural."
                        />
                        <x-form.textarea
                            name="default_meta_keywords"
                            label="Keyword Meta (Opsional)"
                            :value="$settings->default_meta_keywords"
                            rows="2"
                            maxlength="500"
                            hint="Google tidak memakai meta keywords untuk ranking. Isi secukupnya untuk kompatibilitas mesin pencari lain; pisahkan dengan koma."
                        />
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm leading-relaxed text-slate-600">
                            <p><strong class="text-slate-800">Pratinjau tautan:</strong> gambar Open Graph dikelola di Identitas &amp; Branding. Gunakan gambar 1200×630 px, rasio 1.91:1, dengan logo dan teks yang tetap terbaca di mobile.</p>
                            <p class="mt-2">Sistem juga otomatis menyiapkan canonical URL, Open Graph, Twitter Card, structured data, <code>sitemap.xml</code>, dan <code>robots.txt</code>.</p>
                        </div>
                    </div>
                </x-admin.card>
            </section>

            <div class="sticky bottom-4 z-10 flex justify-stretch rounded-2xl border border-slate-200 bg-white/95 p-3 shadow-xl backdrop-blur sm:justify-end">
                <button type="submit" class="admin-button admin-button-primary w-full sm:w-auto">Simpan Pengaturan Website</button>
            </div>
        </form>
    </div>
@endsection
