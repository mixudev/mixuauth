<?php

namespace App\Modules\SSO\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SsoClient extends Model
{
    protected $fillable = [
        'name',
        'oauth_client_id',
        'webhook_url',
        'webhook_secret',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'webhook_secret',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Access areas yang DIBUTUHKAN user agar bisa masuk ke client ini.
     * Jika kosong → client bersifat open (semua user aktif boleh akses).
     */
    public function accessAreas(): BelongsToMany
    {
        return $this->belongsToMany(
            AccessArea::class,
            'sso_client_access_area',
            'sso_client_id',
            'access_area_id'
        )->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Generate webhook secret baru (32 byte hex = 64 chars).
     */
    public static function generateWebhookSecret(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Cek apakah client ini mempunyai required access areas atau tidak.
     * Jika false → client bersifat open, semua user aktif boleh masuk.
     */
    public function requiresAnyAccessArea(): bool
    {
        return $this->accessAreas()->where('access_areas.is_active', true)->exists();
    }

    /**
     * Cek apakah user (berdasarkan array slug area milik user)
     * memenuhi SEMUA required access areas client ini.
     *
     * @param array $userAreaSlugs  Array slug access_area yang dimiliki user
     * @return bool
     */
    public function userHasRequiredAreas(array $userAreaSlugs): bool
    {
        $requiredSlugs = $this->accessAreas()
            ->where('access_areas.is_active', true)
            ->pluck('access_areas.slug')
            ->toArray();

        if (empty($requiredSlugs)) {
            return true; // open client
        }

        // AND logic: user harus punya SEMUA area yang dibutuhkan
        foreach ($requiredSlugs as $slug) {
            if (! in_array($slug, $userAreaSlugs)) {
                return false;
            }
        }

        return true;
    }
}
