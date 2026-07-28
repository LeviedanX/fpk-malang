<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable([
    'address',
    'phone',
    'whatsapp',
    'email',
    'operational_hours',
    'map_embed_url',
    'instagram_url',
    'facebook_url',
    'youtube_url',
    'tiktok_url',
    'singleton_key',
])]
class ContactSetting extends Model
{
    /**
     * The single contact row, or a fresh unsaved instance.
     * Resolved once per request via the container (see AppServiceProvider).
     */
    public static function current(): self
    {
        return app('fpk.contact_setting');
    }

    public static function resolveCurrent(): self
    {
        $attributes = Cache::remember('fpk.contact_setting', 300, fn (): array => static::query()
            ->where('singleton_key', 1)
            ->first()
            ?->attributesToArray() ?? []);

        return new self($attributes ?: ['singleton_key' => 1]);
    }

    /**
     * Whether any public contact channel has been filled in.
     */
    public function hasAnyContact(): bool
    {
        return (bool) ($this->address || $this->phone || $this->whatsapp
            || $this->email || $this->operational_hours
            || $this->instagram_url || $this->facebook_url
            || $this->youtube_url || $this->tiktok_url);
    }

    /**
     * WhatsApp number normalised for wa.me links (digits only).
     */
    public function whatsappLink(): ?string
    {
        if (! $this->whatsapp) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $this->whatsapp);

        return $digits ? "https://wa.me/{$digits}" : null;
    }

    protected static function booted(): void
    {
        static::saved(function (): void {
            Cache::forget('fpk.contact_setting');
            Cache::forget('fpk.public_content_visibility');
        });
        static::deleted(function (): void {
            Cache::forget('fpk.contact_setting');
            Cache::forget('fpk.public_content_visibility');
        });
    }
}
