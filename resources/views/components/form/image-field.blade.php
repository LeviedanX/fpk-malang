@props([
    'name',
    'label',
    'current' => null,
    'hint' => 'Format JPG, PNG, atau WEBP. Maksimal 2 MB.',
])

<div class="space-y-2" x-data="{ fileName: '' }">
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>

    @if ($current)
        <img src="{{ \Illuminate\Support\Facades\Storage::url($current) }}" alt="Pratinjau {{ $label }}" class="h-24 w-auto rounded-xl border border-slate-200 object-cover shadow-sm transition duration-300 hover:scale-[1.03]">
    @endif

    <input
        type="file"
        name="{{ $name }}"
        id="{{ $name }}"
        accept="image/*"
        @change="fileName = $event.target.files[0]?.name || ''"
        {{ $attributes->merge(['class' => 'block w-full cursor-pointer rounded-xl border border-dashed border-slate-300 bg-slate-50 p-2 text-sm text-slate-600 transition hover:border-maroon-300 hover:bg-maroon-50/50 file:mr-3 file:rounded-lg file:border-0 file:bg-maroon-700 file:px-3 file:py-2 file:text-sm file:font-medium file:text-cream-50 hover:file:bg-maroon-800']) }}
    >
    <p x-show="fileName" x-cloak class="truncate text-xs font-medium text-maroon-700" x-text="fileName"></p>

    @if ($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs text-rose-600">{{ $message }}</p>
    @enderror
</div>
