@props(['responsive' => true])

<main
    @class([
        'admin-desktop-notice min-h-dvh place-items-center bg-slate-950 px-5 py-8 text-slate-100',
        'grid lg:hidden' => $responsive,
        'grid' => ! $responsive,
    ])
    data-admin-desktop-notice
>
    <section class="w-full max-w-sm rounded-2xl border border-white/10 bg-slate-900 p-6 text-center shadow-lg shadow-black/20" aria-labelledby="desktop-only-title">
        <span class="mx-auto grid h-11 w-11 place-items-center rounded-xl bg-white/5 text-slate-300 ring-1 ring-white/10" aria-hidden="true">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="4" width="18" height="12" rx="2"/><path stroke-linecap="round" d="M8 20h8M12 16v4"/>
            </svg>
        </span>
        <h1 id="desktop-only-title" class="mt-5 font-sans text-xl font-semibold tracking-tight">Khusus desktop</h1>
        <p class="mt-2 text-sm text-slate-400">Buka panel admin melalui komputer.</p>
        <a href="{{ route('home') }}" class="mt-6 inline-flex min-h-10 items-center justify-center rounded-lg border border-white/10 bg-white/5 px-5 text-sm font-medium text-slate-200 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/20">
            Kembali
        </a>
    </section>
</main>
