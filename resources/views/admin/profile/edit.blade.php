@extends('layouts.admin')

@section('title', 'Profil FPK')
@section('heading', 'Profil FPK')

@section('content')
    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <x-admin.card title="Hero" description="Bagian teratas halaman utama.">
            <div class="space-y-4">
                <x-form.input name="hero_title" label="Judul Hero" :value="$profile->hero_title" required />
                <x-form.input name="hero_subtitle" label="Subjudul Hero" :value="$profile->hero_subtitle" />
                <x-form.image-field name="hero_image" label="Gambar Hero" :current="$profile->hero_image_path" />
            </div>
        </x-admin.card>

        <x-admin.card title="Tentang FPK" description="Isi bagian profil pada halaman utama.">
            <div class="space-y-4">
                <x-form.textarea name="definition" label="Pengertian" :value="$profile->definition" rows="4" hint="Pisahkan antarparagraf dengan baris kosong." />
                <x-form.textarea name="background" label="Latar Belakang" :value="$profile->background" rows="6" hint="Pisahkan antarparagraf dengan baris kosong." />
                <x-form.textarea name="objectives" label="Tujuan" :value="$profile->objectives" rows="5" hint="Satu poin per baris." />
                <x-form.textarea name="core_tasks" label="Tugas Pokok" :value="$profile->core_tasks" rows="5" hint="Satu poin per baris." />
                <x-form.textarea name="legal_basis" label="Dasar Hukum" :value="$profile->legal_basis" rows="5" hint="Satu poin per baris." />
            </div>
        </x-admin.card>

        <div class="flex justify-stretch sm:justify-end">
            <button type="submit" class="admin-button admin-button-primary w-full sm:w-auto">Simpan Perubahan</button>
        </div>
    </form>
@endsection
