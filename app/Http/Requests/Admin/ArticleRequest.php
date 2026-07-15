<?php

namespace App\Http\Requests\Admin;

use App\Enums\PublicationStatus;
use App\Http\Requests\Concerns\HandlesImageRules;
use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
                Rule::unique('articles', 'slug')->ignore($articleId),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'is_featured' => ['boolean'],
            'thumbnail' => $this->imageRules(),
            'status' => ['required', new Enum(PublicationStatus::class)],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
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
