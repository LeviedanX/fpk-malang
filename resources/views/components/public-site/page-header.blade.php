@props(['title', 'subtitle' => null, 'eyebrow' => null])

<section class="relative isolate overflow-hidden bg-maroon-950 text-cream-50">
    <div class="hero-motif parallax-layer pointer-events-none absolute inset-0 -z-10 opacity-40" data-parallax="0.018" aria-hidden="true"></div>
    <div class="hero-glow parallax-layer pointer-events-none absolute inset-0 -z-10" data-parallax="0.035" aria-hidden="true"></div>
    <div class="container-x pb-14 pt-32 text-center sm:pt-36">
        @if ($eyebrow)
            <span class="reveal reveal-scale eyebrow text-gold-400!">{{ $eyebrow }}</span>
        @endif
        <h1 class="reveal mt-3 font-display text-3xl font-extrabold sm:text-4xl lg:text-5xl" style="--reveal-delay: 80ms">{{ $title }}</h1>
        @if ($subtitle)
            <p class="reveal mx-auto mt-4 max-w-2xl text-cream-100/85" style="--reveal-delay: 150ms">{{ $subtitle }}</p>
        @endif
    </div>
</section>
