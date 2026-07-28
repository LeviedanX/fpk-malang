<?php

namespace App\Support;

use Illuminate\Http\Request;

final class SearchTerm
{
    public const MAX_LENGTH = 100;

    public static function fromRequest(Request $request): string
    {
        return mb_substr(trim((string) $request->query('q', '')), 0, self::MAX_LENGTH);
    }

    public static function likePattern(string $term): string
    {
        return '%'.addcslashes($term, '\\%_').'%';
    }
}
