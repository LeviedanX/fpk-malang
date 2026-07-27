<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'device_key',
    'failure_count',
    'lockout_level',
    'locked_until',
])]
class AdminPinSecurityState extends Model
{
    protected function casts(): array
    {
        return [
            'failure_count' => 'integer',
            'lockout_level' => 'integer',
            'locked_until' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
