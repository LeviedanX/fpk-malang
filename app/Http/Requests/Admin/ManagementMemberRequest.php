<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\HandlesImageRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManagementMemberRequest extends FormRequest
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
        return [
            'management_period_id' => ['required', Rule::exists('management_periods', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'division' => ['nullable', 'string', 'max:255'],
            'portrait' => $this->imageRules(),
            'display_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'management_period_id' => 'periode',
            'name' => 'nama',
            'position' => 'jabatan',
            'division' => 'bidang',
            'portrait' => 'foto',
            'display_order' => 'urutan',
            'is_active' => 'status aktif',
        ];
    }
}
