@props([
    'name' => 'images',
    'label' => 'Pilih Foto',
    'hint' => 'Format JPG, PNG, atau WEBP. Maksimal 2 MB per foto.',
    'maxFiles' => 20,
])

<div
    x-data="multiImagePreview"
    class="space-y-3"
    data-multi-image-preview-field="{{ $name }}"
>
    <label for="{{ $name }}" class="block text-sm font-semibold text-slate-700">{{ $label }}</label>

    <div
        x-show="files.length"
        x-cloak
        class="grid grid-cols-2 gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 sm:grid-cols-3 lg:grid-cols-4"
        aria-live="polite"
    >
        <template x-for="file in files" :key="file.url">
            <figure class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <img :src="file.url" :alt="`Preview ${file.name}`" class="aspect-4/3 h-full w-full object-cover">
            </figure>
        </template>
    </div>

    <label
        for="{{ $name }}"
        class="group flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center transition hover:border-maroon-300 hover:bg-maroon-50/50"
    >
        <input
            type="file"
            name="{{ $name }}[]"
            id="{{ $name }}"
            accept="image/jpeg,image/png,image/webp"
            multiple
            class="sr-only"
            x-on:change="selectFiles($event)"
            aria-describedby="{{ $name }}-hint {{ $name }}-selection"
        >
        <span class="grid h-11 w-11 place-items-center rounded-full border border-maroon-100 bg-white text-maroon-700 shadow-sm transition group-hover:border-maroon-200">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5 8.25 11.25a2.25 2.25 0 0 1 3.182 0L16.5 16.318m-1.5-1.5 1.068-1.068a2.25 2.25 0 0 1 3.182 0L21 15.5M14.25 7.5h.008v.008h-.008V7.5ZM5.25 4.5h13.5A2.25 2.25 0 0 1 21 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 17.25V6.75A2.25 2.25 0 0 1 5.25 4.5Z"/>
            </svg>
        </span>
        <span class="mt-3 text-sm font-semibold text-slate-700">Pilih foto galeri</span>
        <span class="mt-1 text-xs text-slate-500">Dapat memilih hingga {{ $maxFiles }} foto sekaligus</span>
    </label>

    <p
        id="{{ $name }}-selection"
        x-show="files.length"
        x-cloak
        class="text-xs font-semibold text-maroon-700"
        x-text="`${files.length} foto siap diunggah`"
    ></p>

    @if ($hint)
        <p id="{{ $name }}-hint" class="text-xs leading-relaxed text-slate-500">{{ $hint }}</p>
    @endif

    @if ($errors->has($name) || $errors->has($name.'.*'))
        <p class="text-xs font-medium text-rose-600">{{ $errors->first($name) ?: $errors->first($name.'.*') }}</p>
    @endif
</div>
