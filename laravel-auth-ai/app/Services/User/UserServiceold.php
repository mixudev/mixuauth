<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function getPaginatedUsers(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->userRepository->getPaginated($perPage, $search);
    }

    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $data['is_active'] ?? true;
        
        return $this->userRepository->create($data);
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function updateUser(User $user, array $data): bool
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        
        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : false;

        return $this->userRepository->update($user, $data);
    }

    public function deleteUser(User $user): bool
    {
        if (auth()->id() === $user->id) {
            throw new \Exception("Anda tidak dapat menghapus akun Anda sendiri.");
        }
        return $this->userRepository->delete($user);
    }
}
