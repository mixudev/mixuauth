<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Kolom yang boleh diisi secara massal
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    /*
    |--------------------------------------------------------------------------
    | Kolom yang disembunyikan dari serialisasi JSON
    |--------------------------------------------------------------------------
    */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Konversi tipe kolom otomatis
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',  // Laravel 11: otomatis hash via bcrypt/argon
    ];

    // -----------------------------------------------------------------------
    // Relasi
    // -----------------------------------------------------------------------

    /**
     * Riwayat login pengguna.
     */
    public function loginLogs(): HasMany
    {
        return $this->hasMany(LoginLog::class);
    }

    /**
     * Daftar perangkat terpercaya milik pengguna.
     */
    public function trustedDevices(): HasMany
    {
        return $this->hasMany(TrustedDevice::class);
    }

    /**
     * Sesi OTP aktif milik pengguna.
     */
    public function otpVerifications(): HasMany
    {
        return $this->hasMany(OtpVerification::class);
    }

    public function userBlocks(): HasMany
    {
        return $this->hasMany(UserBlock::class);
    }

    // -----------------------------------------------------------------------
    // Helper Methods
    // -----------------------------------------------------------------------

    /**
     * Blokir aktif saat ini (jika ada).
     */
    public function activeBlock(): HasOne
    {
        return $this->hasOne(UserBlock::class)->whereNull('unblocked_at')
            ->where(function ($q) {
                $q->whereNull('blocked_until')
                  ->orWhere('blocked_until', '>', now());
            });
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getIsBlockedAttribute(): bool
    {
        return $this->relationLoaded('activeBlock')
            ? $this->activeBlock !== null
            : $this->activeBlock()->exists();
    }

    public function getBlockCountAttribute(): int
    {
        return $this->relationLoaded('userBlocks')
            ? $this->userBlocks->count()
            : $this->userBlocks()->count();
    }


    /**
     * Periksa apakah akun pengguna masih aktif.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Perbarui timestamp dan IP login terakhir.
     */
    public function recordLogin(string $ipAddress): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
