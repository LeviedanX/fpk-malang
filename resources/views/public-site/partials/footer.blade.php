<footer class="relative overflow-hidden bg-maroon-950 text-cream-100">
    <div class="hero-motif pointer-events-none absolute inset-0 opacity-40" aria-hidden="true"></div>

    <div class="container-x relative py-14">
        <div class="grid gap-10 md:grid-cols-3">
            <div class="max-w-sm">
                <div class="flex items-center gap-3">
                    @if ($site->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($site->logo_path) }}" alt="Logo {{ $site->organization_name }}" class="h-11 w-auto" width="44" height="44">
                    @else
                        <span class="grid h-11 w-11 place-items-center rounded-full bg-cream-50 font-display text-lg font-bold text-maroon-800" aria-hidden="true">F</span>
                    @endif
                    <span class="font-display text-lg font-bold text-cream-50">{{ $site->abbreviation ?: 'FPK Kota Malang' }}</span>
                </div>
                <p class="mt-4 text-sm leading-relaxed text-cream-100/75">
                    {{ $site->tagline ?: 'Merawat kebhinnekaan, memperkuat persatuan warga Kota Malang dalam bingkai Negara Kesatuan Republik Indonesia.' }}
                </p>
            </div>

            <div class="text-sm">
                <p class="font-semibold uppercase tracking-wider text-gold-400">Navigasi</p>
                <ul class="mt-4 space-y-2.5 text-cream-100/80">
                    <li><a href="{{ route('home') }}#tentang" class="transition hover:text-white">Tentang FPK</a></li>
                    <li><a href="{{ route('articles.index') }}" class="transition hover:text-white">Artikel</a></li>
                    <li><a href="{{ route('agendas.index') }}" class="transition hover:text-white">Agenda</a></li>
                    <li><a href="{{ route('home') }}#pengurus" class="transition hover:text-white">Susunan Pengurus</a></li>
                    <li><a href="{{ route('home') }}#kontak" class="transition hover:text-white">Kontak</a></li>
                </ul>
            </div>

            <div class="text-sm">
                <p class="font-semibold uppercase tracking-wider text-gold-400">Kontak</p>
                <ul class="mt-4 space-y-2.5 text-cream-100/80">
                    @if ($contact->address)<li>{{ $contact->address }}</li>@endif
                    @if ($contact->email)<li><a href="mailto:{{ $contact->email }}" class="transition hover:text-white">{{ $contact->email }}</a></li>@endif
                    @if ($contact->phone)<li>{{ $contact->phone }}</li>@endif
                    @unless ($contact->hasAnyContact())<li class="text-cream-100/55">Informasi kontak akan segera diperbarui.</li>@endunless
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
        </div>

        <div class="mt-10 flex flex-col items-center gap-3 border-t border-cream-100/15 pt-6 text-center text-xs text-cream-100/65 sm:flex-row sm:justify-between">
            <span>&copy; {{ now()->year }} {{ $site->footer_text ?: $site->organization_name }}.</span>

            <div class="flex items-center gap-3">
                <span>Seluruh hak cipta dilindungi.</span>

                @auth
                    <a href="{{ route('admin.dashboard') }}"
                       aria-label="Dashboard Admin"
                       title="Dashboard Admin"
                       class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-transparent text-cream-100/45 transition hover:bg-cream-100/10 hover:text-cream-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold-400 focus-visible:ring-offset-2 focus-visible:ring-offset-maroon-950">
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
                       class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-transparent text-cream-100/45 transition hover:bg-cream-100/10 hover:text-cream-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold-400 focus-visible:ring-offset-2 focus-visible:ring-offset-maroon-950">
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
