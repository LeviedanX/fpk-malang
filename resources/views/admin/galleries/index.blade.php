@extends('layouts.admin')

@section('title', 'Galeri')
@section('heading', 'Galeri')

@section('content')
    <x-admin.card title="Tambah Foto" description="Unggah foto kegiatan tanpa judul atau deskripsi. Foto baru langsung aktif dan dapat diatur setelah disimpan.">
        <form method="POST" action="{{ route('admin.galleries.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <x-form.multi-image-field
                name="images"
                label="Foto Galeri"
                hint="Format JPG, PNG, atau WEBP. Maksimal 2 MB per foto dan 20 foto per unggahan."
            />
            <div class="flex justify-end">
                <button type="submit" class="admin-button admin-button-primary">Unggah Foto</button>
            </div>
        </form>
    </x-admin.card>

    @if ($galleryImages->isEmpty())
        <x-admin.card>
            <div class="py-10 text-center">
                <p class="font-display text-xl font-semibold text-slate-800">Belum ada foto galeri</p>
                <p class="mt-2 text-sm text-slate-500">Pilih satu atau beberapa foto melalui formulir di atas.</p>
            </div>
        </x-admin.card>
    @else
        <form method="POST" action="{{ route('admin.galleries.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="font-display text-xl font-semibold text-slate-800">Susunan Foto</h2>
                    <p class="mt-1 text-sm text-slate-500">Angka lebih kecil tampil lebih dahulu. Foto nonaktif tidak muncul di website publik.</p>
                </div>
                <button type="submit" class="admin-button admin-button-primary">Simpan Susunan</button>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($galleryImages as $galleryImage)
                    <article class="admin-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-gallery-admin-card>
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::url($galleryImage->image_path) }}"
                            alt="Foto galeri {{ $loop->iteration }}"
                            class="aspect-4/3 w-full bg-slate-100 object-cover"
                            width="640"
                            height="480"
                            loading="lazy"
                            decoding="async"
                        >

                        <div class="space-y-4 p-4">
                            <div class="grid grid-cols-[minmax(0,1fr)_auto] items-end gap-4">
                                <div>
                                    <label for="gallery-order-{{ $galleryImage->id }}" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Urutan tampil</label>
                                    <input
                                        type="number"
                                        name="items[{{ $galleryImage->id }}][display_order]"
                                        id="gallery-order-{{ $galleryImage->id }}"
                                        value="{{ old("items.{$galleryImage->id}.display_order", $galleryImage->display_order) }}"
                                        min="0"
                                        max="100000"
                                        class="form-control mt-1.5 block w-full text-sm"
                                        required
                                    >
                                </div>

                                <label class="flex min-h-11 items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-700">
                                    <input type="hidden" name="items[{{ $galleryImage->id }}][is_visible]" value="0">
                                    <input
                                        type="checkbox"
                                        name="items[{{ $galleryImage->id }}][is_visible]"
                                        value="1"
                                        @checked((bool) old("items.{$galleryImage->id}.is_visible", $galleryImage->is_visible))
                                        class="rounded border-slate-300 text-maroon-700 focus:ring-maroon-600"
                                    >
                                    Tampil
                                </label>
                            </div>

                            <button
                                type="submit"
                                form="delete-gallery-{{ $galleryImage->id }}"
                                class="admin-button admin-button-danger w-full"
                            >Hapus Foto</button>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="flex justify-end">
                <button type="submit" class="admin-button admin-button-primary">Simpan Susunan</button>
            </div>
        </form>

        @foreach ($galleryImages as $galleryImage)
            <form
                id="delete-gallery-{{ $galleryImage->id }}"
                method="POST"
                action="{{ route('admin.galleries.destroy', $galleryImage) }}"
                data-confirm="Foto ini akan dihapus permanen dari galeri dan penyimpanan."
                data-confirm-title="Hapus Foto Galeri?"
                class="hidden"
            >
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    @endif
@endsection
