<?php

namespace App\Modules\Authorization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'group'];

    /**
     * Relasi: Permission dimiliki oleh banyak Role
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    /**
     * Scope untuk filter permission berdasarkan group
     *
     * @param $query
     * @param string $group
     * @return mixed
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Ambil semua group yang tersedia
     *
     * @return array
     */
    public static function getGroups(): array
    {
        return self::distinct()->pluck('group')->toArray();
    }
}
