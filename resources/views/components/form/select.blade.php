@props([
    'name',
    'label',
    'options' => [],
    'selected' => null,
    'required' => false,
    'placeholder' => null,
    'hint' => null,
])

<div class="admin-form-field">
    <label for="{{ $name }}" class="admin-form-label">
        {{ $label }}
        @if ($required)<span class="text-maroon-700">*</span>@endif
    </label>

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'form-control block w-full ' . ($errors->has($name) ? 'border-rose-500' : '')]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) old($name, $selected) === (string) $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @if ($hint)
        <p class="admin-form-hint">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="admin-form-error">{{ $message }}</p>
    @enderror
</div>
