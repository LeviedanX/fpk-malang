<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GalleryUpdateRequest extends FormRequest
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
            'items' => ['required', 'array', 'max:100'],
            'items.*' => ['required', 'array:display_order,is_visible'],
            'items.*.display_order' => ['required', 'integer', 'between:0,100000'],
            'items.*.is_visible' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'items.*.display_order' => 'urutan foto',
            'items.*.is_visible' => 'status tampil foto',
        ];
    }
}
