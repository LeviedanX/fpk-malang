<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GoogleMapsEmbedUrl implements ValidationRule
{
    /**
     * @var list<string>
     */
    private const ALLOWED_HOSTS = [
        'google.com',
        'www.google.com',
        'maps.google.com',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('URL lokasi harus menggunakan URL embed resmi dari Google Maps.');

            return;
        }

        $parts = parse_url($value);
        $host = strtolower($parts['host'] ?? '');
        $path = rtrim($parts['path'] ?? '', '/');
        $query = $parts['query'] ?? '';

        $isStandardEmbed = $path === '/maps/embed' && $query !== '';
        $isLegacyMapsEmbed = $host === 'maps.google.com'
            && $path === '/maps'
            && str_contains('&'.strtolower($query).'&', '&output=embed&');

        if (
            ($parts['scheme'] ?? '') !== 'https'
            || ! in_array($host, self::ALLOWED_HOSTS, true)
            || isset($parts['user'])
            || (isset($parts['port']) && $parts['port'] !== 443)
            || (! $isStandardEmbed && ! $isLegacyMapsEmbed)
        ) {
            $fail('URL lokasi harus menggunakan URL embed resmi dari Google Maps, misalnya https://www.google.com/maps/embed?pb=....');
        }
    }
}
