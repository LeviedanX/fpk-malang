<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'title',
    'slug',
    'excerpt',
    'body',
    'thumbnail_path',
    'is_featured',
    'status',
    'published_at',
    'meta_title',
    'meta_description',
])]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Only articles that are published and whose publish time has arrived.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', PublicationStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeLatestPublished(Builder $query): Builder
    {
        return $query->published()->orderByDesc('published_at');
    }

    /**
     * The article to spotlight on the homepage: the newest published article
     * flagged as featured, falling back to the newest published article.
     * Returns null only when there is no published article at all.
     */
    public static function featuredForHome(): ?self
    {
        $columns = [
            'id', 'title', 'slug', 'excerpt', 'thumbnail_path',
            'is_featured', 'status', 'published_at',
        ];

        return static::query()->select($columns)->latestPublished()->where('is_featured', true)->first()
            ?? static::query()->select($columns)->latestPublished()->first();
    }

    public function isPublished(): bool
    {
        return $this->status === PublicationStatus::Published
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }
}
