@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'hint' => null,
])

<div class="admin-form-field">
    <label for="{{ $name }}" class="admin-form-label">
        {{ $label }}
        @if ($required)<span class="text-maroon-700">*</span>@endif
    </label>

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'form-control block w-full ' . ($errors->has($name) ? 'border-rose-500' : '')]) }}
    >

    @if ($hint)
        <p class="admin-form-hint">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="admin-form-error">{{ $message }}</p>
    @enderror
</div>
