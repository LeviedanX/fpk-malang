<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\HandlesImageRules;
use App\Rules\GoogleMapsEmbedUrl;
use App\Rules\TrustedSocialMediaUrl;
use Illuminate\Foundation\Http\FormRequest;

class SiteSettingRequest extends FormRequest
{
    use HandlesImageRules;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Audio publik selalu opt-in. Nilai ini tidak boleh diaktifkan kembali
        // melalui request yang dibuat manual.
        $this->merge(['background_music_default_playing' => false]);
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
            'admin_login_background' => $this->imageRules(),
            'remove_admin_login_background' => ['nullable', 'boolean'],
            'footer_text' => ['nullable', 'string', 'max:180'],
            'hero_eyebrow' => ['required', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:100'],
            'hero_subtitle' => ['nullable', 'string', 'max:180'],
            'hero_background' => $this->imageRules(),
            'remove_hero_background' => ['nullable', 'boolean'],
            'hero_mobile_background' => $this->imageRules(),
            'remove_hero_mobile_background' => ['nullable', 'boolean'],
            'hero_primary_cta_label' => ['required', 'string', 'max:60'],
            'hero_secondary_cta_label' => ['required', 'string', 'max:60'],
            'hero_legal_basis_label' => ['required', 'string', 'max:60'],
            'hero_foundation_label' => ['required', 'string', 'max:60'],
            'hero_period_label' => ['required', 'string', 'max:60'],
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
            'map_embed_url' => ['nullable', 'url:https', 'max:1000', new GoogleMapsEmbedUrl],
            'instagram_url' => [
                'nullable', 'url:https', 'max:255',
                new TrustedSocialMediaUrl('Instagram', ['instagram.com', 'www.instagram.com']),
            ],
            'facebook_url' => [
                'nullable', 'url:https', 'max:255',
                new TrustedSocialMediaUrl('Facebook', ['facebook.com', 'www.facebook.com', 'm.facebook.com']),
            ],
            'youtube_url' => [
                'nullable', 'url:https', 'max:255',
                new TrustedSocialMediaUrl('YouTube', ['youtube.com', 'www.youtube.com', 'youtu.be']),
            ],
            'tiktok_url' => [
                'nullable', 'url:https', 'max:255',
                new TrustedSocialMediaUrl('TikTok', ['tiktok.com', 'www.tiktok.com']),
            ],
            'default_meta_title' => ['nullable', 'string', 'max:60'],
            'default_meta_description' => ['nullable', 'string', 'max:160'],
            'default_meta_keywords' => ['nullable', 'string', 'max:500'],
            'default_og_image' => $this->imageRules(),
            'background_music' => $this->audioRules(),
            'remove_background_music' => ['nullable', 'boolean'],
            'background_music_visible' => ['required', 'boolean'],
            'background_music_default_playing' => ['required', 'boolean'],
            'background_music_volume' => ['required', 'integer', 'between:0,100'],
        ];
    }

    /**
     * Validation rules for the optional background music upload.
     *
     * @return array<int, mixed>
     */
    protected function audioRules(): array
    {
        $mimes = implode(',', config('fpk.uploads.audio_mimes'));
        $maxSize = (int) config('fpk.uploads.audio_max_size');

        return ['nullable', 'file', "mimes:{$mimes}", "max:{$maxSize}"];
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
            'hero_eyebrow.max' => 'Teks pembuka hero tidak boleh lebih dari 120 karakter.',
            'hero_title.max' => 'Judul hero tidak boleh lebih dari 100 karakter.',
            'hero_subtitle.max' => 'Subtitle hero tidak boleh lebih dari 180 karakter.',
            'hero_primary_cta_label.max' => 'Label tombol utama tidak boleh lebih dari 60 karakter.',
            'hero_secondary_cta_label.max' => 'Label tombol agenda tidak boleh lebih dari 60 karakter.',
            'hero_legal_basis_label.max' => 'Label dasar hukum tidak boleh lebih dari 60 karakter.',
            'hero_foundation_label.max' => 'Label landasan tidak boleh lebih dari 60 karakter.',
            'hero_period_label.max' => 'Label masa bakti tidak boleh lebih dari 60 karakter.',
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
            'admin_login_background' => 'background login admin',
            'remove_admin_login_background' => 'hapus background login admin',
            'footer_text' => 'teks footer',
            'hero_eyebrow' => 'teks pembuka hero',
            'hero_title' => 'judul hero',
            'hero_subtitle' => 'subtitle hero',
            'hero_background' => 'background hero desktop',
            'remove_hero_background' => 'hapus background hero desktop',
            'hero_mobile_background' => 'background hero mobile',
            'remove_hero_mobile_background' => 'hapus background hero mobile',
            'hero_primary_cta_label' => 'label tombol utama',
            'hero_secondary_cta_label' => 'label tombol agenda',
            'hero_legal_basis_label' => 'label dasar hukum',
            'hero_foundation_label' => 'label landasan',
            'hero_period_label' => 'label masa bakti',
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
            'background_music' => 'musik latar',
            'remove_background_music' => 'hapus musik latar',
            'background_music_visible' => 'visibilitas fitur musik',
            'background_music_default_playing' => 'status awal musik',
            'background_music_volume' => 'volume musik',
        ];
    }
}
