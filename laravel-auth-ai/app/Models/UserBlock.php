<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class UserBlock extends Model
{
    protected $fillable = [
        'user_id', 'reason', 'blocked_by',
        'block_count', 'blocked_until',
        'unblocked_at', 'unblocked_by',
    ];

    protected $casts = [
        'blocked_until' => 'datetime',
        'unblocked_at'  => 'datetime',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function blockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function unblockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unblocked_by');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('unblocked_at')
            ->where(function ($q) {
                $q->whereNull('blocked_until')
                  ->orWhere('blocked_until', '>', now());
            });
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return is_null($this->unblocked_at)
            && (is_null($this->blocked_until) || $this->blocked_until->isFuture());
    }
}
