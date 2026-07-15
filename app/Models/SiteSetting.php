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
    'default_og_image_path',
])]
class SiteSetting extends Model
{
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
        ]);
    }
}
