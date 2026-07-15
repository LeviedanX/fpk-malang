@props([
    'name',
    'label',
    'value' => null,
    'required' => false,
    'hint' => 'Gunakan toolbar untuk memformat teks. Lampiran berkas dinonaktifkan; konten disaring otomatis demi keamanan.',
])

<div class="space-y-1">
    <label for="{{ $name }}_trix" class="block text-sm font-medium text-slate-700">
        {{ $label }}
        @if ($required)<span class="text-maroon-700">*</span>@endif
    </label>

    <input id="{{ $name }}_input" type="hidden" name="{{ $name }}" value="{{ old($name, $value) }}">
    <trix-editor
        id="{{ $name }}_trix"
        input="{{ $name }}_input"
        class="trix-content min-h-[16rem] rounded-md border border-slate-300 bg-white {{ $errors->has($name) ? 'border-rose-500' : '' }}"
    ></trix-editor>

    @if ($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs text-rose-600">{{ $message }}</p>
    @enderror
</div>
