<?php

namespace App\Models;

use App\Enums\AgendaStatus;
use App\Enums\PublicationStatus;
use Database\Factories\AgendaFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

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
            ->where(function (Builder $query): void {
                $query
                    ->where(function (Builder $query): void {
                        $query
                            ->where('starts_at', '>=', now())
                            ->where('event_status', '!=', AgendaStatus::Cancelled);
                    })
                    ->orWhere(function (Builder $query): void {
                        $query
                            ->where('starts_at', '<', now())
                            ->where('event_status', '!=', AgendaStatus::Cancelled)
                            ->where(function (Builder $query): void {
                                $query
                                    ->where('ends_at', '>=', now())
                                    ->orWhere(function (Builder $query): void {
                                        $query
                                            ->whereNull('ends_at')
                                            ->where('event_status', '!=', AgendaStatus::Completed);
                                    });
                            });
                    });
            })
            ->orderBy('starts_at');
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->published()
            ->where(function (Builder $query): void {
                $query
                    ->where('ends_at', '<', now())
                    ->orWhere(function (Builder $query): void {
                        $query
                            ->where('starts_at', '<', now())
                            ->whereNull('ends_at')
                            ->whereIn('event_status', [
                                AgendaStatus::Completed,
                                AgendaStatus::Cancelled,
                            ]);
                    });
            })
            ->orderByDesc('starts_at');
    }

    public function scopeVisibleOnPublic(Builder $query): Builder
    {
        return $query->upcoming();
    }

    public function isPublished(): bool
    {
        return $this->publication_status === PublicationStatus::Published
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    public function isVisibleOnPublic(?DateTimeInterface $at = null): bool
    {
        if (! $this->isPublished()) {
            return false;
        }

        $status = $this->effectiveEventStatus($at);

        return in_array($status, [AgendaStatus::Scheduled, AgendaStatus::Ongoing], true);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AgendaLog::class)->latest();
    }

    public function effectiveEventStatus(?DateTimeInterface $at = null): AgendaStatus
    {
        return self::statusForSchedule(
            $this->starts_at,
            $this->ends_at,
            $this->event_status,
            $at,
        );
    }

    public static function statusForSchedule(
        DateTimeInterface|string $startsAt,
        DateTimeInterface|string|null $endsAt,
        AgendaStatus|string $selectedStatus,
        ?DateTimeInterface $at = null,
    ): AgendaStatus {
        $selectedStatus = $selectedStatus instanceof AgendaStatus
            ? $selectedStatus
            : AgendaStatus::from($selectedStatus);

        if ($selectedStatus === AgendaStatus::Cancelled) {
            return AgendaStatus::Cancelled;
        }

        $startsAt = Carbon::parse($startsAt);
        $endsAt = $endsAt ? Carbon::parse($endsAt) : null;
        $at = $at ? Carbon::parse($at) : now();

        if ($at->lt($startsAt)) {
            return AgendaStatus::Scheduled;
        }

        if ($endsAt && $at->gt($endsAt)) {
            return AgendaStatus::Completed;
        }

        if (! $endsAt && $selectedStatus === AgendaStatus::Completed) {
            return AgendaStatus::Completed;
        }

        return AgendaStatus::Ongoing;
    }
}
