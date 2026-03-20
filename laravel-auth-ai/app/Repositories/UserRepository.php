<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function getPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = User::query()->latest();
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }
        
        return $query->paginate($perPage);
    }
    
    public function create(array $data): User
    {
        return User::create($data);
    }
    
    public function findById(int $id): ?User
    {
        return User::findOrFail($id);
    }
    
    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }
    
    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
