<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\HandlesImageRules;
use App\Models\ManagementPeriod;
use Illuminate\Foundation\Http\FormRequest;

class ManagementPeriodRequest extends FormRequest
{
    use HandlesImageRules;

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
        $period = $this->route('period');
        $requiresFutureEndYear = ! $period instanceof ManagementPeriod || $this->boolean('is_active');
        $endYearRules = [
            'required',
            'integer',
            'digits:4',
            'gt:start_year',
        ];

        if ($requiresFutureEndYear) {
            $endYearRules[] = 'min:'.((int) now()->year + 1);
        }

        return [
            'name' => ['required', 'string', 'max:100'],
            'start_year' => ['required', 'integer', 'digits:4', 'min:1945'],
            'end_year' => $endYearRules,
            'group_photo' => $this->imageRules(),
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_year.digits' => 'Tahun mulai wajib berupa angka empat digit.',
            'end_year.required' => 'Tahun selesai wajib diisi.',
            'end_year.digits' => 'Tahun selesai wajib berupa angka empat digit.',
            'end_year.gt' => 'Tahun selesai harus lebih besar dari tahun mulai.',
            'end_year.min' => 'Tahun selesai harus lebih besar dari tahun saat ini ('.now()->year.').',
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
            'group_photo' => 'foto bersama pengurus',
            'is_active' => 'status aktif',
        ];
    }
}
