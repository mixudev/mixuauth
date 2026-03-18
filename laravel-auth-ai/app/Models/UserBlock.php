<?php
// ============================================================
// app/Models/UserBlock.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return is_null($this->unblocked_at)
            && (is_null($this->blocked_until) || $this->blocked_until->isFuture());
    }

    public function scopeActive($query)
    {
        return $query->whereNull('unblocked_at')
            ->where(function ($q) {
                $q->whereNull('blocked_until')
                  ->orWhere('blocked_until', '>', now());
            });
    }
}
