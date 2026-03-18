<?php
// ============================================================
// app/Models/IpBlacklist.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpBlacklist extends Model
{
    protected $table = 'ip_blacklist';

    protected $fillable = [
        'ip_address', 'reason', 'blocked_by',
        'block_count', 'blocked_until', 'blocked_at',
    ];

    protected $casts = [
        'blocked_until' => 'datetime',
        'blocked_at'    => 'datetime',
    ];

    public function isActive(): bool
    {
        return is_null($this->blocked_until)
            || $this->blocked_until->isFuture();
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('blocked_until')
              ->orWhere('blocked_until', '>', now());
        });
    }
}
