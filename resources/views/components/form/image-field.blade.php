@props([
    'name',
    'label',
    'current' => null,
    'fallback' => null,
    'hint' => 'Format JPG, PNG, atau WEBP. Maksimal 2 MB.',
])

@php
    $currentUrl = $current
        ? \Illuminate\Support\Facades\Storage::url($current)
        : $fallback;
    $initialState = $current ? 'current' : ($fallback ? 'default' : 'empty');
@endphp

<div
    class="space-y-2"
    x-data="imagePreview({ initialUrl: @js($currentUrl), initialState: @js($initialState) })"
    data-image-preview-field="{{ $name }}"
>
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>

    <div
        class="relative grid aspect-16/10 w-full max-w-md place-items-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100"
        data-image-preview
    >
        <img
            @if ($currentUrl) src="{{ $currentUrl }}" @endif
            :src="previewUrl || null"
            x-show="previewUrl && ! previewFailed"
            x-cloak
            x-on:error="markPreviewFailed()"
            alt="Pratinjau {{ $label }}"
            class="h-full w-full object-contain"
        >

        <div
            x-show="! previewUrl || previewFailed"
            x-cloak
            class="flex flex-col items-center gap-2 px-4 text-center text-slate-500"
        >
            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm12-11.25h.008v.008h-.008V8.25Z"/>
            </svg>
            <span class="text-xs font-medium" x-text="previewFailed ? 'Preview gambar tidak tersedia' : 'Belum ada gambar'">Belum ada gambar</span>
        </div>

        <span
            x-show="previewUrl && ! previewFailed"
            x-cloak
            x-text="statusLabel()"
            class="absolute bottom-2 left-2 rounded-full bg-slate-950/75 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm backdrop-blur"
            data-image-preview-status
        >{{ $initialState === 'current' ? 'Gambar saat ini' : 'Gambar bawaan' }}</span>
    </div>

    <input
        type="file"
        name="{{ $name }}"
        id="{{ $name }}"
        accept="image/*"
        x-on:change="selectFile($event)"
        aria-describedby="{{ $name }}-hint {{ $name }}-selection"
        {{ $attributes->merge(['class' => 'block w-full cursor-pointer rounded-xl border border-dashed border-slate-300 bg-slate-50 p-2 text-sm text-slate-600 transition hover:border-maroon-300 hover:bg-maroon-50/50 file:mr-3 file:rounded-lg file:border-0 file:bg-maroon-700 file:px-3 file:py-2 file:text-sm file:font-medium file:text-cream-50 hover:file:bg-maroon-800']) }}
    >
    <p
        id="{{ $name }}-selection"
        x-show="fileName"
        x-cloak
        class="truncate text-xs font-medium text-maroon-700"
        aria-live="polite"
    >File dipilih: <span x-text="fileName"></span></p>

    @if ($hint)
        <p id="{{ $name }}-hint" class="text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs text-rose-600">{{ $message }}</p>
    @enderror
</div>
