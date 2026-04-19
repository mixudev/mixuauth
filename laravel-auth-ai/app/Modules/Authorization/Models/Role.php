<?php

namespace App\Modules\Authorization\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    /**
     * Relasi: Role memiliki banyak Permission
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Relasi: Role memiliki banyak User
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role');
    }

    /**
     * Tentukan apakah role memiliki permission tertentu
     *
     * @param string|Permission $permission
     * @return bool
     */
    public function hasPermission($permission): bool
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->first();
        }

        return $this->permissions->contains($permission);
    }

    /**
     * Tambahkan permission ke role
     *
     * @param Permission|array $permission
     * @return $this
     */
    public function addPermission($permission)
    {
        if (is_array($permission)) {
            $this->permissions()->attach($permission);
        } else {
            $this->permissions()->attach($permission->id);
        }

        return $this;
    }

    /**
     * Hapus permission dari role
     *
     * @param Permission|array $permission
     * @return $this
     */
    public function removePermission($permission)
    {
        if (is_array($permission)) {
            $this->permissions()->detach($permission);
        } else {
            $this->permissions()->detach($permission->id);
        }

        return $this;
    }
}
