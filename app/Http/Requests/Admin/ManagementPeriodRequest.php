<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ManagementPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'start_year' => ['required', 'integer', 'min:1945', 'max:2100'],
            'end_year' => ['nullable', 'integer', 'min:1945', 'max:2100', 'gte:start_year'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama periode',
            'start_year' => 'tahun mulai',
            'end_year' => 'tahun berakhir',
            'is_active' => 'status aktif',
        ];
    }
}
