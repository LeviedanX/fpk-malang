<?php

namespace App\Models;

use App\Enums\AgendaStatus;
use App\Enums\PublicationStatus;
use Database\Factories\AgendaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'title',
    'slug',
    'description',
    'poster_path',
    'location',
    'starts_at',
    'ends_at',
    'event_status',
    'publication_status',
    'published_at',
])]
class Agenda extends Model
{
    /** @use HasFactory<AgendaFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'event_status' => AgendaStatus::class,
            'publication_status' => PublicationStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('publication_status', PublicationStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->published()
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at');
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->published()
            ->where('starts_at', '<', now())
            ->orderByDesc('starts_at');
    }

    public function isPublished(): bool
    {
        return $this->publication_status === PublicationStatus::Published
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }
}
