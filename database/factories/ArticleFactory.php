<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = rtrim(fake()->sentence(6), '.');

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'excerpt' => fake()->sentence(18),
            'body' => collect(fake()->paragraphs(4))
                ->map(fn (string $p) => "<p>{$p}</p>")
                ->implode("\n"),
            'thumbnail_path' => null,
            'is_featured' => false,
            'status' => PublicationStatus::Published,
            'published_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => PublicationStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->addWeek(),
        ]);
    }
}
