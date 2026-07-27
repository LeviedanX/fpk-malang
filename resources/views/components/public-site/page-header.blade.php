@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null,
    'backHref' => null,
    'backLabel' => 'Kembali',
    'profile',
    'showBackground' => true,
])

<section @class([
    'relative isolate overflow-hidden text-cream-50',
    'bg-maroon-950' => $showBackground,
])>
    @if ($showBackground)
        <x-public-site.synced-background :profile="$profile" />
    @endif
    <div class="container-x pb-14 pt-32 text-center sm:pt-36">
        @if ($eyebrow)
            <span class="reveal reveal-scale eyebrow text-gold-400!">{{ $eyebrow }}</span>
        @endif
        <h1 class="reveal mt-3 font-display text-3xl font-extrabold sm:text-4xl lg:text-5xl" style="--reveal-delay: 80ms">{{ $title }}</h1>
        @if ($subtitle)
            <p class="reveal mx-auto mt-4 max-w-2xl text-cream-100/85" style="--reveal-delay: 150ms">{{ $subtitle }}</p>
        @endif
        @if ($backHref)
            <a href="{{ $backHref }}" class="reveal reveal-scale btn-ghost-light mt-7" style="--reveal-delay: 220ms">
                <span aria-hidden="true">&larr;</span>
                {{ $backLabel }}
            </a>
        @endif
    </div>
</section>
