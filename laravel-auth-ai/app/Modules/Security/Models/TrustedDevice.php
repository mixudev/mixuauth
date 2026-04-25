<?php

namespace App\Modules\Security\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class TrustedDevice extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Perangkat yang sudah diverifikasi dan dipercaya oleh pengguna.
    | Digunakan untuk mendeteksi login dari perangkat baru.
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'user_id',
        'fingerprint_hash',  // ID unik (Cookie UUID)
        'device_signature',  // Hash kombinasi UA (Browser/OS)
        'device_label',      // Label ramah pengguna, misal: "Chrome on Windows"
        'ip_address',
        'country_code',
        'last_seen_at',
        'trusted_until',
        'is_revoked',
    ];

    protected $casts = [
        'last_seen_at'  => 'datetime',
        'trusted_until' => 'datetime',
        'is_revoked'    => 'boolean',
    ];

    // -----------------------------------------------------------------------
    // Relasi
    // -----------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -----------------------------------------------------------------------
    // Scopes & Helper Methods
    // -----------------------------------------------------------------------

    /**
     * Filter hanya perangkat yang masih aktif dan belum dicabut kepercayaannya.
     */
    public function scopeActive($query)
    {
        return $query
            ->where('is_revoked', false)
            ->where(function($q) {
                $q->whereNull('trusted_until')
                  ->orWhere('trusted_until', '>', now());
            });
    }

    /**
     * Perbarui timestamp terakhir kali perangkat ini digunakan.
     */
    public function touchLastSeen(): void
    {
        $this->update(['last_seen_at' => now()]);
    }

    /**
     * Cabut kepercayaan perangkat ini (force logout dari perangkat tersebut).
     */
    public function revoke(): void
    {
        $this->update(['is_revoked' => true]);
    }

    // -----------------------------------------------------------------------
    // Accessors
    // -----------------------------------------------------------------------

    /**
     * Dapatkan nama browser dari label.
     */
    public function getBrowserNameAttribute(): string
    {
        $parts = explode(' di ', $this->device_label);
        return $parts[0] ?? 'Unknown Browser';
    }

    /**
     * Dapatkan nama OS dari label.
     */
    public function getOsNameAttribute(): string
    {
        $parts = explode(' di ', $this->device_label);
        return $parts[1] ?? 'Unknown OS';
    }

    /**
     * Periksa apakah kepercayaan perangkat telah kedaluwarsa.
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->trusted_until) return false;
        return $this->trusted_until->isPast();
    }

    /**
     * Periksa apakah perangkat masih aktif (tidak dicabut dan tidak kedaluwarsa).
     */
    public function getIsActiveAttribute(): bool
    {
        return !$this->is_revoked && !$this->is_expired;
    }

    /**
     * Tentukan ikon FontAwesome berdasarkan informasi OS/Browser.
     */
    public function getDeviceIconAttribute(): string
    {
        $os = strtolower($this->os_name);
        if (str_contains($os, 'windows')) return 'fa-brands fa-windows';
        if (str_contains($os, 'mac') || str_contains($os, 'ios')) return 'fa-brands fa-apple';
        if (str_contains($os, 'android')) return 'fa-brands fa-android';
        if (str_contains($os, 'linux')) return 'fa-brands fa-linux';
        
        return 'fa-solid fa-laptop';
    }
}
