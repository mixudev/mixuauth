<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
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
        'last_login_country',
        'last_login_ua',
        'last_login_device',
        'timezone',
        'otp_preference',
        'avatar',
        'mfa_enabled',
        'mfa_type',
        'totp_secret',
        'backup_codes',
    ];

    public const OTP_ALWAYS   = 'always';
    public const OTP_SYSTEM   = 'system';
    public const OTP_DISABLED = 'disabled';

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
    | Accessor untuk timezone: pastikan selalu ada nilai (default 'UTC')
    |--------------------------------------------------------------------------
    */
    public function getTimezoneAttribute(?string $value): string
    {
        return $value ?? 'UTC';
    }

    /*
    |--------------------------------------------------------------------------
    | Konversi tipe kolom otomatis
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
        'mfa_enabled'       => 'boolean',
        'backup_codes'      => 'encrypted:array',
    ];

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->avatar)) {
            return \Illuminate\Support\Facades\Storage::url($this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    protected static function booted()
    {
        static::updated(function ($user) {
            // Monitor Password Change
            if ($user->wasChanged('password')) {
                \App\Models\SecurityNotification::create([
                    'user_id'    => $user->id,
                    'type'       => 'warning',
                    'event'      => 'account.password_changed',
                    'title'      => 'Sensitive: Password Diganti',
                    'message'    => 'Password untuk user ' . $user->name . ' (' . $user->email . ') telah diperbarui.',
                    'meta'       => ['field' => 'password'],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            // Monitor Email Change
            if ($user->wasChanged('email')) {
                \App\Models\SecurityNotification::create([
                    'user_id'    => $user->id,
                    'type'       => 'warning',
                    'event'      => 'account.email_changed',
                    'title'      => 'Sensitive: Email Diganti',
                    'message'    => 'Email user ' . $user->getOriginal('name') . ' diubah dari ' . $user->getOriginal('email') . ' menjadi ' . $user->email,
                    'meta'       => [
                        'old_email' => $user->getOriginal('email'),
                        'new_email' => $user->email
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });

        static::deleted(function ($user) {
            \App\Models\SecurityNotification::create([
                'user_id'    => $user->id,
                'type'       => 'error',
                'event'      => 'account.deleted',
                'title'      => 'User Dihapus',
                'message'    => 'Akun user ' . $user->name . ' (' . $user->email . ') telah dihapus dari sistem.',
                'meta'       => ['deleted_at' => now()],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }

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
    public function recordLogin(
        string  $ipAddress,
        ?string $country = null,
        ?string $ua = null,
        ?string $device = null
    ): void {
        $this->update([
            'last_login_at'      => now(),
            'last_login_ip'      => $ipAddress,
            'last_login_country' => $country,
            'last_login_ua'      => $ua,
            'last_login_device'  => $device,
        ]);
    }

    /**
     * Periksa apakah MFA aktif untuk user ini.
     */
    public function hasMfaEnabled(): bool
    {
        return $this->mfa_enabled === true;
    }

    /**
     * Periksa apakah menggunakan TOTP (Authenticator App).
     */
    /**
     * Verifikasi dan hapus kode cadangan yang digunakan.
     */
    public function useBackupCode(string $code): bool
    {
        $codes = $this->backup_codes ?? [];
        $index = array_search($code, $codes);

        if ($index !== false) {
            array_splice($codes, $index, 1);
            $this->update(['backup_codes' => $codes]);
            return true;
        }

        return false;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
