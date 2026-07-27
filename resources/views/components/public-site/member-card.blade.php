@props(['member'])

<article {{ $attributes->class('surface card-lift group flex flex-col overflow-hidden') }}>
    <div class="relative h-80 shrink-0 overflow-hidden bg-linear-to-br from-maroon-100 via-cream-100 to-gold-300/35">
        @if ($member->portrait_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($member->portrait_path) }}"
                 alt="Foto {{ $member->name }}" loading="lazy" width="360" height="450"
                 decoding="async" fetchpriority="low"
                 class="h-full w-full object-cover object-top transition duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.035]">
        @else
            <div class="hero-motif absolute inset-0 opacity-25" aria-hidden="true"></div>
            <span class="relative flex h-full w-full items-center justify-center font-display text-6xl font-bold text-maroon-400/80" aria-hidden="true">
                {{ \Illuminate\Support\Str::of($member->name)->substr(0, 1)->upper() }}
            </span>
        @endif
        <span class="absolute inset-x-0 bottom-0 h-20 bg-linear-to-t from-black/45 to-transparent" aria-hidden="true"></span>
    </div>

    <div class="flex flex-1 flex-col p-5 text-center">
        @if ($member->division)
            <p class="mb-2 flex h-8 items-center justify-center text-[10px] font-semibold uppercase leading-4 tracking-[0.16em] text-gold-600">
                {{ $member->division }}
            </p>
        @else
            <span class="mb-2 block h-8" aria-hidden="true"></span>
        @endif
        <h3 class="flex min-h-14 items-center justify-center font-display text-lg font-bold leading-snug text-maroon-800">{{ $member->name }}</h3>
        <p class="mt-auto pt-1 text-sm font-medium text-maroon-700">{{ $member->position }}</p>
    </div>
</article>
