@props([
    'name',
    'label',
    'checked' => false,
    'hint' => null,
])

<div class="space-y-1">
    <label class="group flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/70 p-3 transition hover:border-maroon-200 hover:bg-maroon-50/50">
        <input type="hidden" name="{{ $name }}" value="0">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $name }}"
            value="1"
            @checked(old($name, $checked))
            {{ $attributes->merge(['class' => 'h-4 w-4 rounded border-slate-300 text-maroon-700 focus:ring-maroon-600']) }}
        >
        <span class="pt-0.5 text-sm font-medium text-slate-700 transition group-hover:text-maroon-800">{{ $label }}</span>
    </label>

    @if ($hint)
        <p class="ml-1 text-xs leading-relaxed text-slate-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="ml-7 text-xs text-rose-600">{{ $message }}</p>
    @enderror
</div>
