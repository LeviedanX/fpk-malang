<?php

namespace App\Http\Requests\Admin;

use App\Enums\AgendaStatus;
use App\Enums\PublicationStatus;
use App\Http\Requests\Concerns\HandlesImageRules;
use App\Models\Agenda;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class AgendaRequest extends FormRequest
{
    use HandlesImageRules;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $slug = filled($this->slug) ? $this->slug : $this->title;

        $this->merge([
            'slug' => str($slug ?? '')->slug()->value(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $agenda = $this->route('agenda');
        $agendaId = $agenda instanceof Agenda ? $agenda->id : null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('agendas', 'slug')->ignore($agendaId),
            ],
            'description' => ['nullable', 'string'],
            'poster' => $this->imageRules(),
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'event_status' => ['required', new Enum(AgendaStatus::class)],
            'publication_status' => ['required', new Enum(PublicationStatus::class)],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'judul',
            'slug' => 'slug',
            'description' => 'deskripsi',
            'poster' => 'poster',
            'location' => 'lokasi',
            'starts_at' => 'waktu mulai',
            'ends_at' => 'waktu selesai',
            'event_status' => 'status acara',
            'publication_status' => 'status publikasi',
            'published_at' => 'waktu terbit',
        ];
    }
}
