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
            'site_name' => ['required', 'string', 'max:255'],
            'organization_name' => ['required', 'string', 'max:255'],
            'abbreviation' => ['nullable', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'logo' => $this->imageRules(),
            'favicon' => ['nullable', 'file', 'mimes:png,ico', 'max:512'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'default_meta_title' => ['nullable', 'string', 'max:255'],
            'default_meta_description' => ['nullable', 'string', 'max:500'],
            'default_og_image' => $this->imageRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'site_name' => 'nama situs',
            'organization_name' => 'nama organisasi',
            'abbreviation' => 'singkatan',
            'tagline' => 'tagline',
            'logo' => 'logo',
            'favicon' => 'favicon',
            'footer_text' => 'teks footer',
            'default_meta_title' => 'meta title default',
            'default_meta_description' => 'meta description default',
            'default_og_image' => 'gambar OG default',
        ];
    }
}
