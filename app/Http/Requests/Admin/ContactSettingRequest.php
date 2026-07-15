<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ContactSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'operational_hours' => ['nullable', 'string', 'max:255'],
            'map_embed_url' => ['nullable', 'url', 'max:1000'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'address' => 'alamat',
            'phone' => 'telepon',
            'whatsapp' => 'WhatsApp',
            'email' => 'email',
            'operational_hours' => 'jam operasional',
            'map_embed_url' => 'URL peta',
            'instagram_url' => 'Instagram',
            'facebook_url' => 'Facebook',
            'youtube_url' => 'YouTube',
            'tiktok_url' => 'TikTok',
        ];
    }
}
