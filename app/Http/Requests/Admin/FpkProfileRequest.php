<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\HandlesImageRules;
use Illuminate\Foundation\Http\FormRequest;

class FpkProfileRequest extends FormRequest
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
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:255'],
            'hero_image' => $this->imageRules(),
            'definition' => ['nullable', 'string', 'max:5000'],
            'background' => ['nullable', 'string', 'max:8000'],
            'objectives' => ['nullable', 'string', 'max:5000'],
            'core_tasks' => ['nullable', 'string', 'max:5000'],
            'legal_basis' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'hero_title' => 'judul hero',
            'hero_subtitle' => 'subjudul hero',
            'hero_image' => 'gambar hero',
            'definition' => 'pengertian',
            'background' => 'latar belakang',
            'objectives' => 'tujuan',
            'core_tasks' => 'tugas pokok',
            'legal_basis' => 'dasar hukum',
        ];
    }
}
