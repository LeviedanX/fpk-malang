<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

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
        return static::query()->first() ?? new self([
            'site_name' => config('app.name'),
            'organization_name' => 'Forum Pembauran Kebangsaan Kota Malang',
            'abbreviation' => 'FPK Kota Malang',
            'background_music_visible' => true,
            'background_music_default_playing' => true,
            'background_music_volume' => 50,
            'background_music_preference_version' => 1,
        ]);
    }
}
