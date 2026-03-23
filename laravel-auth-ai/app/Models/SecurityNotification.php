<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'event',
        'title',
        'message',
        'meta',
        'ip_address',
        'user_agent',
        'read_at',
    ];

    protected $casts = [
        'meta'    => 'array',
        'read_at' => 'datetime',
    ];

    protected $appends = ['time_ago', 'is_read'];

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at?->diffForHumans() ?? '';
    }

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}
