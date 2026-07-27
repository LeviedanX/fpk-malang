@php
    $sameAs = collect([
        $contact->instagram_url ?? null,
        $contact->facebook_url ?? null,
        $contact->youtube_url ?? null,
        $contact->tiktok_url ?? null,
    ])->filter()->values();

    $logoPath = $site->logo_path
        ? \Illuminate\Support\Facades\Storage::url($site->logo_path)
        : null;
    $logoUrl = $logoPath
        ? (\Illuminate\Support\Str::startsWith($logoPath, ['http://', 'https://']) ? $logoPath : url($logoPath))
        : null;
    $organizationId = url('/').'#organization';
    $websiteId = url('/').'#website';

    $organization = array_filter([
        '@type' => 'Organization',
        '@id' => $organizationId,
        'name' => $site->organization_name,
        'alternateName' => $site->abbreviation ?: null,
        'url' => url('/'),
        'logo' => $logoUrl ? [
            '@type' => 'ImageObject',
            'url' => $logoUrl,
        ] : null,
        'description' => $site->default_meta_description ?: null,
        'address' => ($contact->address ?? null) ? [
            '@type' => 'PostalAddress',
            'streetAddress' => $contact->address,
            'addressLocality' => 'Kota Malang',
            'addressRegion' => 'Jawa Timur',
            'addressCountry' => 'ID',
        ] : null,
        'email' => $contact->email ?? null,
        'telephone' => $contact->phone ?? null,
        'sameAs' => $sameAs->isNotEmpty() ? $sameAs->all() : null,
    ], fn ($value) => ! is_null($value));

    $website = [
        '@type' => 'WebSite',
        '@id' => $websiteId,
        'url' => url('/'),
        'name' => $site->site_name,
        'inLanguage' => 'id-ID',
        'publisher' => ['@id' => $organizationId],
    ];

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => [$organization, $website],
    ];
@endphp
<script type="application/ld+json" nonce="{{ request()->attributes->get('csp_nonce') }}">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
