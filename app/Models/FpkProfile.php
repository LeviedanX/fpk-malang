<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable([
    'hero_eyebrow',
    'hero_title',
    'hero_subtitle',
    'hero_background_path',
    'hero_mobile_background_path',
    'hero_primary_cta_label',
    'hero_secondary_cta_label',
    'hero_legal_basis_label',
    'hero_foundation_label',
    'hero_period_label',
    'about_image_path',
    'institution_legal_basis',
    'institution_foundation',
    'definition',
    'background',
    'objectives',
    'core_tasks',
    'legal_basis',
    'singleton_key',
])]
class FpkProfile extends Model
{
    /**
     * The single profile row, or a fresh unsaved instance.
     */
    public static function current(): self
    {
        return app('fpk.profile');
    }

    public static function resolveCurrent(): self
    {
        $attributes = Cache::remember('fpk.profile', 300, fn (): array => static::query()
            ->where('singleton_key', 1)
            ->first()
            ?->attributesToArray() ?? []);

        return new self($attributes ?: [
            'hero_eyebrow' => 'Forum Pembauran Kebangsaan Kota Malang',
            'hero_title' => 'Forum Pembauran Kebangsaan Kota Malang',
            'hero_primary_cta_label' => 'Tentang FPK',
            'hero_secondary_cta_label' => 'Lihat Agenda',
            'hero_legal_basis_label' => 'Dasar Hukum',
            'hero_foundation_label' => 'Landasan',
            'hero_period_label' => 'Masa Bakti',
            'singleton_key' => 1,
        ]);
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('fpk.profile'));
        static::deleted(fn () => Cache::forget('fpk.profile'));
    }
}
