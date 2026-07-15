@props(['icon' => true])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-maroon-200 bg-white/60 px-6 py-14 text-center text-ink-500 dark:border-ink-700 dark:bg-ink-900/40 dark:text-ink-400']) }}>
    @if ($icon)
        <svg class="mx-auto mb-3 h-9 w-9 text-maroon-200 dark:text-ink-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
    @endif
    <p class="text-sm">{{ $slot }}</p>
</div>
