@props(['title' => null, 'description' => null])

<section {{ $attributes->merge(['class' => 'admin-card rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-6']) }}>
    @if ($title)
        <header class="mb-5 flex items-stretch gap-3 border-b border-slate-100 pb-4">
            <span class="admin-card-accent" aria-hidden="true"></span>
            <div class="min-w-0">
                <h2 class="font-display text-lg font-semibold text-slate-800">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
                @endif
            </div>
        </header>
    @endif

    {{ $slot }}
</section>
