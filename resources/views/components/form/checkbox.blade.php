@props([
    'name',
    'label',
    'checked' => false,
    'hint' => null,
])

<div class="space-y-1">
    <label class="flex items-center gap-3">
        <input type="hidden" name="{{ $name }}" value="0">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $name }}"
            value="1"
            @checked(old($name, $checked))
            {{ $attributes->merge(['class' => 'h-4 w-4 rounded border-slate-300 text-maroon-700 focus:ring-maroon-600']) }}
        >
        <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
    </label>

    @if ($hint)
        <p class="ml-7 text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="ml-7 text-xs text-rose-600">{{ $message }}</p>
    @enderror
</div>
