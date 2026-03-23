<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpVerification extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Model OTP untuk verifikasi dua langkah saat risk_score menengah.
    | Setiap baris hanya berlaku untuk satu sesi verifikasi.
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'user_id',
        'token',            // Hash dari kode OTP, bukan kode mentah
        'session_token',    // Token sesi pending yang menunggu verifikasi OTP
        'expires_at',
        'attempts',
        'verified_at',
        'ip_address',
        'device_fingerprint',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'verified_at' => 'datetime',
        'attempts'    => 'integer',
    ];

    protected static function booted()
    {
        static::updated(function ($otp) {
            // Monitor OTP Exhaustion
            if ($otp->wasChanged('attempts') && $otp->isExhausted()) {
                \App\Models\SecurityNotification::create([
                    'user_id'    => $otp->user_id,
                    'type'       => 'error',
                    'event'      => 'auth.otp_exhausted',
                    'title'      => 'Percobaan OTP Habis',
                    'message'    => 'Percobaan verifikasi OTP untuk user telah mencapai batas maksimal (Suspicious).',
                    'meta'       => ['attempts' => $otp->attempts, 'user_id' => $otp->user_id],
                    'ip_address' => $otp->ip_address ?? request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });
    }

    protected $hidden = [
        'token', // Hash OTP tidak boleh terekspos ke luar
    ];

    // -----------------------------------------------------------------------
    // Relasi
    // -----------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -----------------------------------------------------------------------
    // Helper Methods
    // -----------------------------------------------------------------------

    /**
     * Periksa apakah OTP masih dalam masa berlaku.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Periksa apakah batas percobaan verifikasi sudah terlampaui.
     */
    public function isExhausted(): bool
    {
        $maxAttempts = config('security.otp.max_attempts', 3);
        return $this->attempts >= $maxAttempts;
    }

    /**
     * Periksa apakah OTP sudah pernah diverifikasi sebelumnya.
     */
    public function isAlreadyUsed(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Tandai OTP sebagai telah diverifikasi.
     */
    public function markAsVerified(): void
    {
        $this->update(['verified_at' => now()]);
    }

    /**
     * Tambah hitungan percobaan verifikasi yang gagal.
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }
}
