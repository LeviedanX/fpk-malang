<?php

namespace App\Http\Requests\Admin;

use App\Enums\PublicationStatus;
use App\Http\Requests\Concerns\HandlesImageRules;
use App\Models\Article;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ArticleRequest extends FormRequest
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
        $article = $this->route('article');
        $articleId = $article instanceof Article ? $article->id : null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                $this->uniqueSlugRule($articleId),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:200000'],
            'is_featured' => ['boolean'],
            'thumbnail' => $this->imageRules(),
            'status' => ['required', new Enum(PublicationStatus::class)],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Slug harus unik terhadap seluruh baris, termasuk artikel yang diarsipkan,
     * karena indeks unik di database ikut menghitung baris soft-deleted. Tanpa
     * penjelasan ini admin melihat "sudah digunakan" untuk artikel yang tidak
     * tampak di mana pun selain Arsip.
     */
    private function uniqueSlugRule(?int $articleId): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($articleId): void {
            $existing = Article::withTrashed()
                ->when($articleId, fn ($query) => $query->whereKeyNot($articleId))
                ->where('slug', $value)
                ->first();

            if (! $existing) {
                return;
            }

            $fail($existing->trashed()
                ? 'Slug ini masih dipakai artikel di Arsip ("'.$existing->title.'"). Pulihkan atau hapus permanen artikel tersebut, atau pakai slug lain.'
                : 'Slug ini sudah dipakai artikel lain ("'.$existing->title.'").');
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
            'excerpt' => 'ringkasan',
            'body' => 'isi artikel',
            'is_featured' => 'artikel unggulan',
            'thumbnail' => 'gambar sampul',
            'status' => 'status',
            'published_at' => 'waktu terbit',
            'meta_title' => 'meta title',
            'meta_description' => 'meta description',
        ];
    }
}
