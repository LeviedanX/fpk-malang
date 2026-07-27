<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TrustedSocialMediaUrl implements ValidationRule
{
    /**
     * @param  list<string>  $allowedHosts
     */
    public function __construct(
        private readonly string $platform,
        private readonly array $allowedHosts,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail("URL {$this->platform} tidak valid.");

            return;
        }

        $parts = parse_url($value);
        $host = strtolower($parts['host'] ?? '');

        if (($parts['scheme'] ?? '') !== 'https'
            || ! in_array($host, $this->allowedHosts, true)
            || isset($parts['user'])
            || isset($parts['pass'])
            || (isset($parts['port']) && $parts['port'] !== 443)) {
            $fail("URL harus menggunakan alamat HTTPS resmi {$this->platform}.");
        }
    }
}
