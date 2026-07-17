<?php

namespace App\Models;

use Database\Factories\ManagementPeriodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'start_year', 'end_year', 'group_photo_path', 'is_active'])]
class ManagementPeriod extends Model
{
    /** @use HasFactory<ManagementPeriodFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'start_year' => 'integer',
            'end_year' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(ManagementMember::class);
    }

    /**
     * Members that should be shown publicly, in display order.
     */
    public function activeMembers(): HasMany
    {
        return $this->members()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function label(): string
    {
        return $this->end_year
            ? "{$this->start_year}-{$this->end_year}"
            : (string) $this->start_year;
    }
}
