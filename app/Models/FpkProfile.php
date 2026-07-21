<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'hero_title',
    'hero_subtitle',
    'hero_image_path',
    'about_image_path',
    'institution_legal_basis',
    'institution_foundation',
    'definition',
    'background',
    'objectives',
    'core_tasks',
    'legal_basis',
])]
class FpkProfile extends Model
{
    /**
     * The single profile row, or a fresh unsaved instance.
     */
    public static function current(): self
    {
        return static::query()->first() ?? new self([
            'hero_title' => 'Forum Pembauran Kebangsaan Kota Malang',
        ]);
    }
}
