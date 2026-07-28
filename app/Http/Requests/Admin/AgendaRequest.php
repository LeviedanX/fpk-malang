<?php

namespace App\Http\Requests\Admin;

use App\Enums\AgendaStatus;
use App\Enums\PublicationStatus;
use App\Http\Requests\Concerns\HandlesImageRules;
use App\Models\Agenda;
use Closure;
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
                $this->uniqueSlugRule($agendaId),
            ],
            'description' => ['nullable', 'string', 'max:20000'],
            'poster' => $this->imageRules(),
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'event_status' => ['required', new Enum(AgendaStatus::class)],
            'publication_status' => ['required', new Enum(PublicationStatus::class)],
            'published_at' => [
                'nullable',
                'date',
                Rule::when(
                    $this->input('publication_status') === PublicationStatus::Published->value
                        && $this->filled('ends_at'),
                    ['before_or_equal:ends_at'],
                ),
            ],
        ];
    }

    /**
     * Slug harus unik terhadap seluruh baris, termasuk agenda yang diarsipkan,
     * karena indeks unik di database ikut menghitung baris soft-deleted. Tanpa
     * penjelasan ini admin melihat "sudah digunakan" untuk agenda yang tidak
     * tampak di mana pun selain Arsip.
     */
    private function uniqueSlugRule(?int $agendaId): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($agendaId): void {
            $existing = Agenda::withTrashed()
                ->when($agendaId, fn ($query) => $query->whereKeyNot($agendaId))
                ->where('slug', $value)
                ->first();

            if (! $existing) {
                return;
            }

            $fail($existing->trashed()
                ? 'Slug ini masih dipakai agenda di Arsip ("'.$existing->title.'"). Pulihkan atau hapus permanen agenda tersebut, atau pakai slug lain.'
                : 'Slug ini sudah dipakai agenda lain ("'.$existing->title.'").');
        };
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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ends_at.after' => 'Waktu selesai harus lebih besar daripada waktu mulai.',
            'published_at.before_or_equal' => 'Waktu terbit harus sebelum atau tepat saat agenda selesai.',
        ];
    }
}
