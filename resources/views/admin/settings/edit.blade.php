@extends('layouts.admin')

@section('title', 'Pengaturan Website')
@section('heading', 'Pengaturan Website')

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <x-admin.card title="Identitas">
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input name="site_name" label="Nama Situs" :value="$settings->site_name" required />
                    <x-form.input name="organization_name" label="Nama Organisasi" :value="$settings->organization_name" required />
                    <x-form.input name="abbreviation" label="Singkatan" :value="$settings->abbreviation" />
                    <x-form.input name="tagline" label="Tagline" :value="$settings->tagline" />
                </div>
                <x-form.textarea name="footer_text" label="Teks Footer" :value="$settings->footer_text" rows="2" />
            </div>
        </x-admin.card>

        <x-admin.card title="Branding">
            <div class="grid gap-6 md:grid-cols-3">
                <x-form.image-field name="logo" label="Logo" :current="$settings->logo_path" />
                <x-form.image-field name="favicon" label="Favicon" :current="$settings->favicon_path" hint="Format PNG atau ICO. Maksimal 512 KB." />
                <x-form.image-field name="default_og_image" label="Gambar OG Default" :current="$settings->default_og_image_path" />
            </div>
        </x-admin.card>

        <x-admin.card title="SEO Default">
            <div class="space-y-4">
                <x-form.input name="default_meta_title" label="Meta Title Default" :value="$settings->default_meta_title" />
                <x-form.textarea name="default_meta_description" label="Meta Description Default" :value="$settings->default_meta_description" rows="2" />
            </div>
        </x-admin.card>

        <div class="flex justify-stretch sm:justify-end">
            <button type="submit" class="admin-button admin-button-primary w-full sm:w-auto">Simpan Perubahan</button>
        </div>
    </form>
@endsection
