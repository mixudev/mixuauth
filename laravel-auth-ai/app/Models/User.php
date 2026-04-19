<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Authorization\Models\Role;
use App\Modules\Authorization\Models\Permission;
use App\Modules\Security\Models\LoginLog;
use App\Modules\Security\Models\TrustedDevice;
use App\Modules\Identity\Models\UserBlock;
use App\Modules\Authentication\Models\OtpVerification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Cache permission slugs untuk lifecycle request ini.
     *
     * @var array<string>|null
     */
    protected ?array $cachedPermissionSlugs = null;

    /**
     * [M-01 FIX] Cache role slugs per-request untuk menghindari query database berulang.
     * Setiap pemanggilan hasRole() sebelumnya selalu hit database.
     *
     * @var array<string>|null
     */
    protected ?array $cachedRoleSlugs = null;

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
        'session_version',
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
        'totp_secret',       // Jangan ekspos TOTP secret ke klien
        'backup_codes',      // Jangan ekspos backup codes ke klien
        'session_version',   // Informasi internal sesi
        'otp_preference',    // Preferensi internal
        'mfa_type',          // Informasi internal MFA
        'last_login_ip',     // PII — jangan ekspos di JSON
        'last_login_ua',     // PII — jangan ekspos di JSON
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
        'totp_secret'       => 'encrypted',  // [H-04 FIX] Enkripsi TOTP secret di database
        'session_version'   => 'integer',
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
                \App\Modules\Security\Models\SecurityNotification::create([
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
                \App\Modules\Security\Models\SecurityNotification::create([
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
            \App\Modules\Security\Models\SecurityNotification::create([
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
        return $this->hasMany(\App\Modules\Authentication\Models\OtpVerification::class);
    }

    public function userBlocks(): HasMany
    {
        return $this->hasMany(UserBlock::class);
    }

    /**
     * Relasi: User memiliki banyak Role
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    /**
     * Ambil semua permission yang dimiliki user melalui roles-nya
     */
    public function permissions()
    {
        return Permission::whereIn(
            'id',
            $this->roles()->with('permissions')->get()->pluck('permissions.*.id')->flatten()->unique()
        )->get();
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

    public function isAdmin(): bool
    {
        // Primary source of truth: RBAC roles
        if ($this->hasRole(['super-admin', 'admin'])) {
            return true;
        }

        // Backward compatible fallback (bootstrap / emergency access)
        return in_array(
            strtolower($this->email),
            config('security.admin_emails', []),
            true
        );
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
     * Tentukan apakah user memiliki role tertentu.
     * [M-01 FIX] Menggunakan cache in-memory per-request agar tidak hit DB setiap kali.
     *
     * @param string|array $roleSlug
     */
    public function hasRole($roleSlug): bool
    {
        if (is_array($roleSlug)) {
            foreach ($roleSlug as $slug) {
                if ($this->hasRole($slug)) {
                    return true;
                }
            }
            return false;
        }

        return in_array($roleSlug, $this->getCachedRoleSlugs(), true);
    }

    /**
     * Ambil semua role slug milik user dan cache per-request.
     *
     * @return array<string>
     */
    protected function getCachedRoleSlugs(): array
    {
        if (is_array($this->cachedRoleSlugs)) {
            return $this->cachedRoleSlugs;
        }

        $this->cachedRoleSlugs = $this->roles()
            ->pluck('slug')
            ->values()
            ->all();

        return $this->cachedRoleSlugs;
    }

    /**
     * Tentukan apakah user memiliki semua role tertentu
     *
     * @param array $roleSlugs
     * @return bool
     */
    public function hasAllRoles(array $roleSlugs): bool
    {
        foreach ($roleSlugs as $slug) {
            if (!$this->hasRole($slug)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Tentukan apakah user memiliki permission tertentu
     *
     * @param string|Permission $permission
     * @return bool
     */
    public function hasPermission($permission): bool
    {
        $slug = null;

        if (is_string($permission)) {
            $slug = trim($permission);
        } elseif ($permission instanceof Permission) {
            $slug = $permission->slug;
        }

        if (!is_string($slug) || $slug === '') {
            return false;
        }

        // Fast path: cached slugs
        return in_array($slug, $this->getCachedPermissionSlugs(), true);
    }

    /**
     * Ambil semua permission slug user (melalui roles) dan cache per-request.
     *
     * @return array<string>
     */
    protected function getCachedPermissionSlugs(): array
    {
        if (is_array($this->cachedPermissionSlugs)) {
            return $this->cachedPermissionSlugs;
        }

        $this->cachedPermissionSlugs = Permission::query()
            ->select('permissions.slug')
            ->join('role_permission', 'role_permission.permission_id', '=', 'permissions.id')
            ->join('user_role', 'user_role.role_id', '=', 'role_permission.role_id')
            ->where('user_role.user_id', $this->id)
            ->distinct()
            ->pluck('permissions.slug')
            ->values()
            ->all();

        return $this->cachedPermissionSlugs;
    }

    /**
     * Tentukan apakah user memiliki semua permission tertentu
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Tambahkan role ke user
     *
     * @param Role|string $role
     * @return $this
     */
    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->first();
        }

        if ($role && !$this->hasRole($role->slug)) {
            $this->roles()->attach($role->id);
        }

        return $this;
    }

    /**
     * Hapus role dari user
     *
     * @param Role|string $role
     * @return $this
     */
    public function removeRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->first();
        }

        if ($role) {
            $this->roles()->detach($role->id);
        }

        return $this;
    }

    /**
     * Ganti semua role user
     *
     * @param array $roleSlugs
     * @return $this
     */
    public function syncRoles(array $roleSlugs)
    {
        $roleIds = Role::whereIn('slug', $roleSlugs)->pluck('id')->toArray();
        $this->roles()->sync($roleIds);
        return $this;
    }

    /**
     * Periksa apakah menggunakan TOTP (Authenticator App).
     */
    /**
     * Verifikasi dan hapus kode cadangan yang digunakan.
     *
     * SECURITY: Backup codes disimpan sebagai SHA-256 hash.
     * Perbandingan dilakukan dengan hash_equals() untuk mencegah timing attack.
     */
    public function useBackupCode(string $code): bool
    {
        $codes = $this->backup_codes ?? [];
        $inputHash = hash('sha256', strtoupper(trim($code)));

        foreach ($codes as $index => $storedValue) {
            // Support plaintext legacy codes (jika ada data lama) dan hashed codes
            $storedHash = (strlen($storedValue) === 64 && ctype_xdigit($storedValue))
                ? $storedValue                          // Sudah berupa SHA-256 hash
                : hash('sha256', strtoupper(trim($storedValue))); // Legacy plaintext

            if (hash_equals($storedHash, $inputHash)) {
                array_splice($codes, $index, 1);
                $this->update(['backup_codes' => $codes]);
                return true;
            }
        }

        return false;
    }

    /**
     * Generate backup codes baru dalam bentuk hashed (SHA-256).
     * Kembalikan array berisi plain codes (untuk ditampilkan SEKALI ke user)
     * dan hashed codes (untuk disimpan ke database).
     *
     * @return array{plain: string[], hashed: string[]}
     */
    public static function generateHashedBackupCodes(int $count = 8): array
    {
        $plain  = [];
        $hashed = [];

        for ($i = 0; $i < $count; $i++) {
            $code     = strtoupper(\Illuminate\Support\Str::random(10));
            $plain[]  = $code;
            $hashed[] = hash('sha256', $code);
        }

        return ['plain' => $plain, 'hashed' => $hashed];
    }

    // [L-02 FIXED] Self-referential user() method dihapus — tidak memiliki semantik yang valid.
}
