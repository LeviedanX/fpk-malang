<?php

namespace App\Http\Requests\Chat;

use App\Rules\CleanMessage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared validation for both sides of the chat: a message needs text, an
 * image, or both — but never neither.
 */
class ChatMessageRequest extends FormRequest
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
            'body' => ['nullable', 'string', 'max:2000', ...$this->moderationRules()],
            'image' => [
                'nullable',
                'file',
                'image',
                'mimes:'.implode(',', config('fpk.uploads.mimes')),
                'max:'.config('fpk.uploads.max_size'),
                'dimensions:max_width='.config('fpk.uploads.max_width')
                    .',max_height='.config('fpk.uploads.max_height'),
            ],
        ];
    }

    /**
     * Penyaring kata hanya berlaku untuk tamu.
     *
     * Admin adalah satu-satunya pihak yang terautentikasi pada alur chat, dan
     * ia memang perlu bisa mengutip kalimat kasar dari tamu saat menanggapi
     * atau mendokumentasikan sebuah laporan.
     *
     * @return list<CleanMessage>
     */
    private function moderationRules(): array
    {
        return $this->user() === null ? [app(CleanMessage::class)] : [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (blank($this->input('body')) && ! $this->hasFile('image')) {
                $validator->errors()->add('body', 'Tulis pesan atau lampirkan gambar terlebih dahulu.');
            }
        });
    }

    public function messageBody(): ?string
    {
        $body = trim((string) $this->input('body', ''));

        return $body === '' ? null : $body;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'body' => 'pesan',
            'image' => 'gambar',
        ];
    }
}
