<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\HandlesImageRules;
use Illuminate\Foundation\Http\FormRequest;

class SiteSettingRequest extends FormRequest
{
    use HandlesImageRules;

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
            'settings_section' => ['nullable', 'in:identitas,beranda,tentang,kontak,seo'],
            'site_name' => ['required', 'string', 'max:60'],
            'organization_name' => ['required', 'string', 'max:100'],
            'abbreviation' => ['nullable', 'string', 'max:20'],
            'tagline' => ['nullable', 'string', 'max:120'],
            'logo' => $this->imageRules(),
            'favicon' => ['nullable', 'file', 'mimes:png,ico', 'max:512'],
            'footer_text' => ['nullable', 'string', 'max:180'],
            'hero_title' => ['required', 'string', 'max:100'],
            'hero_subtitle' => ['nullable', 'string', 'max:180'],
            'hero_image' => $this->imageRules(),
            'institution_legal_basis' => ['nullable', 'string', 'max:120'],
            'institution_foundation' => ['nullable', 'string', 'max:120'],
            'definition' => ['nullable', 'string', 'max:5000'],
            'background' => ['nullable', 'string', 'max:8000'],
            'objectives' => ['nullable', 'string', 'max:5000'],
            'core_tasks' => ['nullable', 'string', 'max:5000'],
            'legal_basis' => ['nullable', 'string', 'max:5000'],
            'about_image' => $this->imageRules(),
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
            'default_meta_title' => ['nullable', 'string', 'max:70'],
            'default_meta_description' => ['nullable', 'string', 'max:160'],
            'default_meta_keywords' => ['nullable', 'string', 'max:500'],
            'default_og_image' => $this->imageRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'site_name.max' => 'Nama situs tidak boleh lebih dari 60 karakter.',
            'organization_name.max' => 'Nama organisasi tidak boleh lebih dari 100 karakter.',
            'abbreviation.max' => 'Singkatan tidak boleh lebih dari 20 karakter.',
            'tagline.max' => 'Tagline tidak boleh lebih dari 120 karakter.',
            'hero_title.max' => 'Judul hero tidak boleh lebih dari 100 karakter.',
            'hero_subtitle.max' => 'Subtitle hero tidak boleh lebih dari 180 karakter.',
            'footer_text.max' => 'Teks footer tidak boleh lebih dari 180 karakter.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'settings_section' => 'bagian pengaturan',
            'site_name' => 'nama situs',
            'organization_name' => 'nama organisasi',
            'abbreviation' => 'singkatan',
            'tagline' => 'tagline',
            'logo' => 'logo',
            'favicon' => 'favicon',
            'footer_text' => 'teks footer',
            'hero_title' => 'judul hero',
            'hero_subtitle' => 'subtitle hero',
            'hero_image' => 'gambar hero',
            'institution_legal_basis' => 'dasar hukum singkat',
            'institution_foundation' => 'landasan lembaga',
            'definition' => 'pengertian',
            'background' => 'latar belakang',
            'objectives' => 'tujuan',
            'core_tasks' => 'tugas pokok',
            'legal_basis' => 'dasar hukum',
            'about_image' => 'ilustrasi Tentang FPK',
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
            'default_meta_title' => 'meta title default',
            'default_meta_description' => 'meta description default',
            'default_meta_keywords' => 'keyword default',
            'default_og_image' => 'gambar OG default',
        ];
    }
}
