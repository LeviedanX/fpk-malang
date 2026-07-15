<?php

namespace App\Http\Requests\Concerns;

trait HandlesImageRules
{
    /**
     * Validation rules for an optional uploaded image, driven by config/fpk.php.
     *
     * @return array<int, mixed>
     */
    protected function imageRules(bool $required = false): array
    {
        $mimes = implode(',', config('fpk.uploads.mimes'));
        $maxSize = (int) config('fpk.uploads.max_size');
        $maxWidth = (int) config('fpk.uploads.max_width');
        $maxHeight = (int) config('fpk.uploads.max_height');

        return [
            $required ? 'required' : 'nullable',
            'image',
            "mimes:{$mimes}",
            "max:{$maxSize}",
            "dimensions:max_width={$maxWidth},max_height={$maxHeight}",
        ];
    }
}
