@props([
    'name',
    'label',
    'value' => null,
    'rows' => 4,
    'required' => false,
    'hint' => null,
])

<div class="admin-form-field">
    <label for="{{ $name }}" class="admin-form-label">
        {{ $label }}
        @if ($required)<span class="text-maroon-700">*</span>@endif
    </label>

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'form-control block w-full resize-y ' . ($errors->has($name) ? 'border-rose-500' : '')]) }}
    >{{ old($name, $value) }}</textarea>

    @if ($hint)
        <p class="admin-form-hint">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="admin-form-error">{{ $message }}</p>
    @enderror
</div>
