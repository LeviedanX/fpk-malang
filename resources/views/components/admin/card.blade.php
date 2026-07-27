@props(['title' => null, 'description' => null, 'actionHref' => null, 'actionLabel' => null])

<section {{ $attributes->merge(['class' => 'admin-card rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-6']) }}>
    @if ($title)
        <header class="mb-5 flex items-stretch justify-between gap-3 border-b border-slate-100 pb-4">
            <div class="flex min-w-0 items-stretch gap-3">
                <span class="admin-card-accent" aria-hidden="true"></span>
                <div class="min-w-0">
                    <h2 class="font-display text-lg font-semibold leading-tight text-slate-900">{{ $title }}</h2>
                    @if ($description)
                        <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500">{{ $description }}</p>
                    @endif
                </div>
            </div>
            @if ($actionHref && $actionLabel)
                <a href="{{ $actionHref }}" class="admin-card-action flex-none self-center">
                    {{ $actionLabel }} <span aria-hidden="true">&rarr;</span>
                </a>
            @endif
        </header>
    @endif

    {{ $slot }}
</section>
