@extends('layouts.admin')

@section('title', 'Kontak & Media Sosial')
@section('heading', 'Kontak & Media Sosial')

@section('content')
    <form method="POST" action="{{ route('admin.contact.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <x-admin.card title="Informasi Kontak" description="Isi hanya data resmi yang telah terverifikasi.">
            <div class="space-y-4">
                <x-form.textarea name="address" label="Alamat" :value="$contact->address" rows="3" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input name="phone" label="Telepon" :value="$contact->phone" />
                    <x-form.input name="whatsapp" label="WhatsApp" :value="$contact->whatsapp" hint="Contoh: 6281234567890" />
                    <x-form.input name="email" label="Email" type="email" :value="$contact->email" />
                    <x-form.input name="operational_hours" label="Jam Operasional" :value="$contact->operational_hours" />
                </div>
                <x-form.input name="map_embed_url" label="URL Embed Peta" :value="$contact->map_embed_url" hint="URL embed Google Maps (https://...)." />
            </div>
        </x-admin.card>

        <x-admin.card title="Media Sosial">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-form.input name="instagram_url" label="Instagram" :value="$contact->instagram_url" />
                <x-form.input name="facebook_url" label="Facebook" :value="$contact->facebook_url" />
                <x-form.input name="youtube_url" label="YouTube" :value="$contact->youtube_url" />
                <x-form.input name="tiktok_url" label="TikTok" :value="$contact->tiktok_url" />
            </div>
        </x-admin.card>

        <div class="flex justify-end">
            <button type="submit" class="rounded-md bg-maroon-700 px-6 py-2.5 font-medium text-cream-50 hover:bg-maroon-800">Simpan Perubahan</button>
        </div>
    </form>
@endsection
