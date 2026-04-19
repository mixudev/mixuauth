<?php

namespace App\Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
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

    protected static function booted()
    {
        static::created(function ($block) {
            \App\Modules\Security\Models\SecurityNotification::create([
                'user_id'    => $block->user_id,
                'type'       => 'error',
                'event'      => 'account.blocked',
                'title'      => 'Akun Diblokir',
                'message'    => 'User ' . $block->user->name . ' (' . $block->user->email . ') telah diblokir. Alasan: ' . ($block->reason ?? 'Tidak ada alasan spesifik') . ($block->blocked_until ? '. Sampai: ' . $block->blocked_until->format('d M Y H:i') : '. Permanen.'),
                'meta'       => ['reason' => $block->reason, 'until' => $block->blocked_until],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        static::updated(function ($block) {
            if ($block->wasChanged('unblocked_at') && $block->unblocked_at !== null) {
                \App\Modules\Security\Models\SecurityNotification::create([
                    'user_id'    => $block->user_id,
                    'type'       => 'success',
                    'event'      => 'account.unblocked',
                    'title'      => 'Blokir Dibuka (Unblock)',
                    'message'    => 'Akses untuk user ' . $block->user->name . ' (' . $block->user->email . ') telah dipulihkan/dibuka kuncinya.',
                    'meta'       => ['unblocked_at' => $block->unblocked_at],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });
    }

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
