@props(['agenda'])

<article class="surface card-lift flex gap-5 p-5">
    <div class="flex-none text-center">
        <div class="rounded-lg bg-maroon-700 px-3 py-2 text-cream-50 shadow-sm">
            <span class="block font-display text-2xl font-bold leading-none">{{ $agenda->starts_at->translatedFormat('d') }}</span>
            <span class="mt-0.5 block text-xs uppercase tracking-wide">{{ $agenda->starts_at->translatedFormat('M') }}</span>
        </div>
        <span class="mt-1 block text-xs text-ink-400">{{ $agenda->starts_at->translatedFormat('Y') }}</span>
    </div>

    <div class="min-w-0 flex-1">
        <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium {{ $agenda->event_status->badgeClasses() }}">
            {{ $agenda->event_status->label() }}
        </span>

        <h3 class="mt-1.5 font-display text-base font-bold text-ink-800 dark:text-cream-100">
            <a href="{{ route('agendas.show', $agenda) }}" class="transition hover:text-maroon-700 dark:hover:text-gold-400">{{ $agenda->title }}</a>
        </h3>

        <dl class="mt-1.5 space-y-1 text-sm text-ink-500 dark:text-ink-400">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 flex-none text-maroon-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <dd>{{ $agenda->starts_at->translatedFormat('l, d F Y · H.i') }} WIB</dd>
            </div>
            @if ($agenda->location)
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 flex-none text-maroon-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <dd class="truncate">{{ $agenda->location }}</dd>
                </div>
            @endif
        </dl>
    </div>
</article>
