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
        // Gunakan format string waktu yang eksplisit (UTC) untuk menghindari
        // masalah presisi atau timezone mismatch di tingkat database.
        return $query
            ->where('is_revoked', false)
            ->where('trusted_until', '>', now()->utc()->toDateTimeString());
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
}
