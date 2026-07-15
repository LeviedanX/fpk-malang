<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

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
        return static::query()->first() ?? new self;
    }

    /**
     * Whether any public contact channel has been filled in.
     */
    public function hasAnyContact(): bool
    {
        return (bool) ($this->address || $this->phone || $this->whatsapp
            || $this->email || $this->instagram_url || $this->facebook_url
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
}
