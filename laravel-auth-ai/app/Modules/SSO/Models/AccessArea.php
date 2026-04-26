<?php

namespace App\Modules\SSO\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;

class AccessArea extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * User yang di-assign ke area ini.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_access_area');
    }

    /**
     * SSO Clients yang MEMBUTUHKAN area ini sebagai syarat akses.
     */
    public function ssoClients(): BelongsToMany
    {
        return $this->belongsToMany(
            SsoClient::class,
            'sso_client_access_area',
            'access_area_id',
            'sso_client_id'
        )->withTimestamps();
    }
}
