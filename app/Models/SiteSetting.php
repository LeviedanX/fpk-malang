<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable([
    'site_name',
    'organization_name',
    'abbreviation',
    'tagline',
    'logo_path',
    'favicon_path',
    'footer_text',
    'default_meta_title',
    'default_meta_description',
    'default_meta_keywords',
    'default_og_image_path',
    'admin_login_background_path',
    'background_music_path',
    'background_music_visible',
    'background_music_default_playing',
    'background_music_volume',
    'background_music_preference_version',
    'singleton_key',
])]
class SiteSetting extends Model
{
    protected function casts(): array
    {
        return [
            'background_music_visible' => 'boolean',
            'background_music_default_playing' => 'boolean',
            'background_music_volume' => 'integer',
            'background_music_preference_version' => 'integer',
        ];
    }

    /**
     * The single settings row, or a fresh unsaved instance with sane defaults.
     * Resolved once per request via the container (see AppServiceProvider).
     */
    public static function current(): self
    {
        return app('fpk.site_setting');
    }

    public static function resolveCurrent(): self
    {
        $attributes = Cache::remember('fpk.site_setting', 300, fn (): array => static::query()
            ->where('singleton_key', 1)
            ->first()
            ?->attributesToArray() ?? []);

        return new self($attributes ?: [
            'site_name' => config('app.name'),
            'organization_name' => 'Forum Pembauran Kebangsaan Kota Malang',
            'abbreviation' => 'FPK Kota Malang',
            'background_music_visible' => true,
            'background_music_default_playing' => false,
            'background_music_volume' => 50,
            'background_music_preference_version' => 1,
            'singleton_key' => 1,
        ]);
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('fpk.site_setting'));
        static::deleted(fn () => Cache::forget('fpk.site_setting'));
    }
}
