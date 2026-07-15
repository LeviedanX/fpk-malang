@props([
    'name',
    'label',
    'value' => null,
    'rows' => 4,
    'required' => false,
    'hint' => null,
])

<div class="space-y-1">
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">
        {{ $label }}
        @if ($required)<span class="text-maroon-700">*</span>@endif
    </label>

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'block w-full rounded-md border-slate-300 shadow-sm focus:border-maroon-600 focus:ring-maroon-600 ' . ($errors->has($name) ? 'border-rose-500' : '')]) }}
    >{{ old($name, $value) }}</textarea>

    @if ($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs text-rose-600">{{ $message }}</p>
    @enderror
</div>
