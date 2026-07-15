@props(['member', 'featured' => false])

@php($avatar = $featured ? 'h-28 w-28' : 'h-24 w-24')

<div class="surface card-lift flex flex-col items-center p-5 text-center">
    <div class="{{ $avatar }} overflow-hidden rounded-full bg-maroon-100 ring-4 ring-cream-100 dark:bg-ink-800 dark:ring-ink-800">
        @if ($member->portrait_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($member->portrait_path) }}"
                 alt="Foto {{ $member->name }}" loading="lazy" width="112" height="112"
                 class="h-full w-full object-cover">
        @else
            <span class="flex h-full w-full items-center justify-center font-display text-2xl text-maroon-400" aria-hidden="true">
                {{ \Illuminate\Support\Str::of($member->name)->substr(0, 1)->upper() }}
            </span>
        @endif
    </div>
    <p class="mt-4 font-semibold text-ink-800 dark:text-cream-100">{{ $member->name }}</p>
    <p class="mt-0.5 text-sm text-maroon-700 dark:text-gold-400">{{ $member->position }}</p>
</div>
