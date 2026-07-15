@php
    $sameAs = collect([
        $contact->instagram_url ?? null,
        $contact->facebook_url ?? null,
        $contact->youtube_url ?? null,
        $contact->tiktok_url ?? null,
    ])->filter()->values();

    $jsonLd = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'GovernmentOrganization',
        'name' => $site->organization_name,
        'alternateName' => $site->abbreviation ?: null,
        'url' => url('/'),
        'logo' => $site->logo_path ? \Illuminate\Support\Facades\Storage::url($site->logo_path) : null,
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
@endphp
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
