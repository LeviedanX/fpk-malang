<footer class="relative overflow-hidden bg-maroon-950 text-cream-100">
    <div class="hero-motif parallax-layer pointer-events-none absolute inset-0 opacity-40" data-parallax="0.012" aria-hidden="true"></div>

    <div class="container-x relative py-14">
        <div class="grid gap-10 {{ $contact->hasAnyContact() ? 'md:grid-cols-3' : 'md:grid-cols-2' }}">
            <div class="reveal reveal-left max-w-sm">
                <div class="flex items-center gap-3">
                    @if ($site->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($site->logo_path) }}" alt="Logo {{ $site->organization_name }}" class="h-11 w-auto" width="44" height="44" loading="lazy" decoding="async">
                    @else
                        <span class="grid h-11 w-11 place-items-center overflow-hidden rounded-full bg-cream-50 p-1 shadow-sm ring-1 ring-gold-400/35" aria-hidden="true">
                            <img src="{{ asset('assets/images/branding/logo-fpk.png') }}" alt="" class="h-full w-full object-contain" width="44" height="44" loading="lazy" decoding="async">
                        </span>
                    @endif
                    <span class="font-display text-lg font-bold text-cream-50">{{ $site->abbreviation ?: $site->site_name }}</span>
                </div>
                @if ($site->tagline)
                    <p class="mt-4 text-sm leading-relaxed text-cream-100/75">{{ $site->tagline }}</p>
                @endif
            </div>

            <div class="reveal text-sm" style="--reveal-delay: 70ms">
                <p class="font-semibold uppercase tracking-wider text-gold-400">Navigasi</p>
                <ul class="mt-4 space-y-2.5 text-cream-100/80">
                    <li><a href="{{ route('home') }}#tentang" class="transition hover:text-white">Tentang FPK</a></li>
                    @if ($publicContentVisibility['articles'])
                        <li><a href="{{ route('home') }}#artikel" class="transition hover:text-white">Artikel</a></li>
                    @endif
                    @if ($publicContentVisibility['agendas'])
                        <li><a href="{{ route('home') }}#agenda" class="transition hover:text-white">Agenda</a></li>
                    @endif
                    @if ($publicContentVisibility['management'])
                        <li><a href="{{ route('home') }}#pengurus" class="transition hover:text-white">Susunan Pengurus</a></li>
                    @endif
                    @if ($publicContentVisibility['contact'])
                        <li><a href="{{ route('home') }}#kontak" class="transition hover:text-white">Kontak</a></li>
                    @endif
                </ul>
            </div>

            @if ($contact->hasAnyContact())
                <div class="reveal reveal-right text-sm" style="--reveal-delay: 140ms">
                    <p class="font-semibold uppercase tracking-wider text-gold-400">Kontak</p>
                    <ul class="mt-4 space-y-2.5 text-cream-100/80">
                        @if ($contact->address)<li>{{ $contact->address }}</li>@endif
                        @if ($contact->email)<li><a href="mailto:{{ $contact->email }}" class="transition hover:text-white">{{ $contact->email }}</a></li>@endif
                        @if ($contact->phone)<li>{{ $contact->phone }}</li>@endif
                        @if ($contact->whatsappLink())<li><a href="{{ $contact->whatsappLink() }}" target="_blank" rel="noopener noreferrer" class="transition hover:text-white">{{ $contact->whatsapp }}</a></li>@endif
                        @if ($contact->operational_hours)<li>{{ $contact->operational_hours }}</li>@endif
                    </ul>

                    @if ($contact->instagram_url || $contact->facebook_url || $contact->youtube_url || $contact->tiktok_url)
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach (['instagram_url' => 'Instagram', 'facebook_url' => 'Facebook', 'youtube_url' => 'YouTube', 'tiktok_url' => 'TikTok'] as $field => $label)
                                @if ($contact->{$field})
                                    <a href="{{ $contact->{$field} }}" target="_blank" rel="noopener noreferrer" class="rounded-md border border-cream-100/25 px-3 py-1 text-xs transition hover:border-gold-400 hover:text-white">{{ $label }}</a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="reveal mt-10 flex flex-col items-center gap-3 border-t border-cream-100/15 pt-6 text-center text-xs text-cream-100/65 sm:flex-row sm:justify-between">
            <span>&copy; {{ now()->year }} {{ $site->footer_text ?: $site->organization_name }}.</span>

            <div class="flex items-center gap-3">
                <span>Seluruh hak cipta dilindungi.</span>

                @auth
                    <a href="{{ route('admin.dashboard') }}"
                       aria-label="Dashboard Admin"
                       title="Dashboard Admin"
                       class="hidden h-9 w-9 items-center justify-center rounded-full bg-transparent text-cream-100/45 transition hover:bg-cream-100/10 hover:text-cream-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold-400 focus-visible:ring-offset-2 focus-visible:ring-offset-maroon-950 lg:inline-flex">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4.25 w-4.25" aria-hidden="true">
                            <rect x="5" y="10" width="14" height="10" rx="2" />
                            <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                            <circle cx="12" cy="15" r="1.2" />
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       aria-label="Login Admin"
                       title="Login Admin"
                       class="hidden h-9 w-9 items-center justify-center rounded-full bg-transparent text-cream-100/45 transition hover:bg-cream-100/10 hover:text-cream-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold-400 focus-visible:ring-offset-2 focus-visible:ring-offset-maroon-950 lg:inline-flex">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4.25 w-4.25" aria-hidden="true">
                            <rect x="5" y="10" width="14" height="10" rx="2" />
                            <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                            <circle cx="12" cy="15" r="1.2" />
                        </svg>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</footer>
