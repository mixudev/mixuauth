<?php

namespace App\Modules\Identity\Services;

use App\Models\User;
use App\Modules\Identity\Models\UserBlock;
use App\Modules\Authentication\Services\PasswordResetService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class UserService
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService
    ) {}
    /**
     * Ambil daftar user dengan filter, search, dan sort.
     */
    public function getUsers(array $filters = []): LengthAwarePaginator
    {
        $query = User::withTrashed()
            ->with(['activeBlock'])
            ->withCount(['loginLogs', 'userBlocks']);

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('last_login_ip', 'like', "%{$search}%");
            });
        }

        // Filter status
        match ($filters['status'] ?? 'all') {
            'active'   => $query->whereNull('deleted_at')->where('is_active', true)
                                ->whereDoesntHave('activeBlock'),
            'inactive' => $query->whereNull('deleted_at')->where('is_active', false)
                                ->whereDoesntHave('activeBlock'),
            'blocked'  => $query->whereHas('activeBlock'),
            'deleted'  => $query->whereNotNull('deleted_at'),
            default    => $query->whereNull('deleted_at'),
        };

        // Sort
        $sortMap = [
            'name'           => ['name', 'asc'],
            '-name'          => ['name', 'desc'],
            '-last_login_at' => ['last_login_at', 'desc'],
            '-created_at'    => ['created_at', 'desc'],
            'created_at'     => ['created_at', 'asc'],
            'block_count'    => ['user_blocks_count', 'desc'],
        ];

        [$col, $dir] = $sortMap[$filters['sort'] ?? '-created_at'];
        $query->orderBy($col, $dir);

        $perPage = in_array((int)($filters['per_page'] ?? 15), [10, 25, 50, 100])
            ? (int) $filters['per_page']
            : 15;

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Statistik ringkasan untuk stat cards.
     */
    public function getSummaryStats(): array
    {
        return [
            'total'    => User::count(),
            'active'   => User::where('is_active', true)
                              ->whereDoesntHave('activeBlock')
                              ->count(),
            'blocked'  => UserBlock::active()->count(),
            'inactive' => User::where('is_active', false)
                              ->whereDoesntHave('activeBlock')
                              ->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
            'new_today'  => User::whereDate('created_at', today())->count(),
        ];
    }

    /**
     * Buat user baru.
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'              => $data['name'],
                'email'             => $data['email'],
                'password'          => Hash::make($data['password']),
                'is_active'         => (bool) ($data['is_active'] ?? true),
                'email_verified_at' => isset($data['email_verified']) && $data['email_verified']
                    ? now()
                    : null,
            ]);

            return $user;
        });
    }

    /**
     * Update data user.
     */
    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $payload = [
                'name'      => $data['name'],
                'email'     => $data['email'],
                'is_active' => (bool) ($data['is_active'] ?? $user->is_active),
            ];

            // Email verified toggle
            if (isset($data['email_verified'])) {
                $payload['email_verified_at'] = $data['email_verified']
                    ? ($user->email_verified_at ?? now())
                    : null;
            }

            // Update password jika diisi
            if (!empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);

            return $user->fresh();
        });
    }

    /**
     * Blokir user.
     */
    public function blockUser(User $user, array $data, ?int $blockedBy = null): UserBlock
    {
        return DB::transaction(function () use ($user, $data, $blockedBy) {
            // Nonaktifkan blokir lama jika ada
            $user->userBlocks()->active()->update(['unblocked_at' => now()]);

            $blockCount = $user->userBlocks()->count() + 1;

            $block = UserBlock::create([
                'user_id'       => $user->id,
                'reason'        => $data['reason'],
                'blocked_by'    => $blockedBy,
                'block_count'   => $blockCount,
                'blocked_until' => !empty($data['blocked_until']) ? $data['blocked_until'] : null,
            ]);

            // Nonaktifkan user
            $user->forceFill([
                'is_active' => false,
                'session_version' => $user->session_version + 1,
                'remember_token' => \Illuminate\Support\Str::random(60),
            ])->save();
            DB::table('sessions')->where('user_id', $user->id)->delete();

            return $block;
        });
    }

    /**
     * Unblokir user.
     */
    public function unblockUser(User $user, ?int $unblockedBy = null): void
    {
        DB::transaction(function () use ($user, $unblockedBy) {
            $user->userBlocks()->active()->update([
                'unblocked_at' => now(),
                'unblocked_by' => $unblockedBy,
            ]);

            $user->update(['is_active' => true]);
        });
    }

    /**
     * Soft delete user.
     */
    public function deleteUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Blokir semua sesi aktif
            $user->userBlocks()->active()->update(['unblocked_at' => now()]);
            $user->increment('session_version');
            $user->forceFill(['remember_token' => \Illuminate\Support\Str::random(60)])->save();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->delete();
        });
    }

    /**
     * Restore soft-deleted user.
     */
    public function restoreUser(User $user): void
    {
        $user->restore();
        $user->update(['is_active' => true]);
    }

    /**
     * Kirim link reset password (menggunakan servis kustom).
     */
    public function sendPasswordReset(User $user): bool
    {
        try {
            $token = $this->passwordResetService->createToken($user->email);
            $user->notify(new \App\Modules\Authentication\Notifications\ResetPasswordNotification($token, $user->email));
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Bulk block/unblock.
     */
    public function bulkBlock(array $userIds, string $reason, ?int $blockedBy = null): int
    {
        $count = 0;
        $users = User::whereIn('id', $userIds)->get();
        foreach ($users as $user) {
            $this->blockUser($user, ['reason' => $reason], $blockedBy);
            $count++;
        }
        return $count;
    }

    public function bulkUnblock(array $userIds, ?int $unblockedBy = null): int
    {
        $count = 0;
        $users = User::whereIn('id', $userIds)->get();
        foreach ($users as $user) {
            $this->unblockUser($user, $unblockedBy);
            $count++;
        }
        return $count;
    }
}
