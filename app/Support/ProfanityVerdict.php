<?php

namespace App\Support;

/**
 * Hasil pemeriksaan satu pesan oleh App\Support\ProfanityFilter.
 */
final readonly class ProfanityVerdict
{
    /**
     * @param  list<string>  $matches  istilah yang memicu penolakan, untuk log dan pengujian
     */
    private function __construct(
        public bool $blocked,
        public ?string $category = null,
        public ?string $message = null,
        public array $matches = [],
    ) {}

    public static function clean(): self
    {
        return new self(false);
    }

    /**
     * @param  list<string>  $matches
     */
    public static function blocked(string $category, string $message, array $matches = []): self
    {
        return new self(true, $category, $message, $matches);
    }
}
