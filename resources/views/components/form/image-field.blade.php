@props([
    'name',
    'label',
    'current' => null,
    'hint' => 'Format JPG, PNG, atau WEBP. Maksimal 2 MB.',
])

<div class="space-y-2">
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>

    @if ($current)
        <img src="{{ \Illuminate\Support\Facades\Storage::url($current) }}" alt="Pratinjau {{ $label }}" class="h-24 w-auto rounded-md border border-slate-200 object-cover">
    @endif

    <input
        type="file"
        name="{{ $name }}"
        id="{{ $name }}"
        accept="image/*"
        {{ $attributes->merge(['class' => 'block w-full text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-maroon-700 file:px-4 file:py-2 file:text-sm file:font-medium file:text-cream-50 hover:file:bg-maroon-800']) }}
    >

    @if ($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs text-rose-600">{{ $message }}</p>
    @enderror
</div>
