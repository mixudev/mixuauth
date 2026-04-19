<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Security\BlockUserRequest;
use App\Http\Requests\Security\StoreUserRequest;
use App\Http\Requests\Security\UpdateUserRequest;
use App\Models\User;
use App\Modules\Identity\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
        $this->authorizeResource(User::class, 'user');
    }

    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $this->authorize('manage', User::class);
        $filters = $request->only(['search', 'status', 'sort', 'per_page']);

        $users = $this->userService->getUsers($filters);
        $stats = $this->userService->getSummaryStats();

        return view('identity::users.index', compact('users', 'stats', 'filters'));
    }

    // ─── Store ─────────────────────────────────────────────────────────────────

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('manage', User::class);
        try {
            $user = $this->userService->createUser($request->validated());

            return response()->json([
                'success' => true,
                'message' => "Pengguna {$user->name} berhasil dibuat.",
                'user'    => $this->formatUser($user),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal membuat pengguna.'], 500);
        }
    }

    // ─── Update ────────────────────────────────────────────────────────────────

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            $updated = $this->userService->updateUser($user, $request->validated());

            return response()->json([
                'success' => true,
                'message' => "Data {$updated->name} berhasil diperbarui.",
                'user'    => $this->formatUser($updated),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui data.'], 500);
        }
    }

    // ─── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus akun sendiri.'], 403);
        }

        try {
            $name = $user->name;
            $this->userService->deleteUser($user);

            return response()->json([
                'success' => true,
                'message' => "{$name} berhasil dihapus.",
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus pengguna.'], 500);
        }
    }

    // ─── Block ─────────────────────────────────────────────────────────────────

    public function block(BlockUserRequest $request, User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat memblokir diri sendiri.'], 403);
        }

        try {
            $this->userService->blockUser($user, $request->validated(), auth()->id());

            return response()->json([
                'success' => true,
                'message' => "{$user->name} berhasil diblokir.",
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memblokir pengguna.'], 500);
        }
    }

    // ─── Unblock ───────────────────────────────────────────────────────────────

    public function unblock(User $user): JsonResponse
    {
        try {
            $this->userService->unblockUser($user, auth()->id());

            return response()->json([
                'success' => true,
                'message' => "{$user->name} berhasil di-unblokir.",
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal meng-unblokir pengguna.'], 500);
        }
    }

    // ─── Reset Password ────────────────────────────────────────────────────────

    public function resetPassword(User $user): JsonResponse
    {
        try {
            $success = $this->userService->sendPasswordReset($user);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => "Link reset password berhasil dikirim ke {$user->email}.",
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Gagal mengirim email reset.'], 500);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat mengirim link reset.'], 500);
        }
    }

    // ─── Bulk Actions ──────────────────────────────────────────────────────────

    public function bulkAction(Request $request): JsonResponse
    {
        $this->authorize('manage', User::class);
        $request->validate([
            'action'   => ['required', 'in:block,unblock,delete'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer'],
            'reason'   => ['required_if:action,block', 'nullable', 'string'],
        ]);

        $ids    = $request->input('user_ids');
        $action = $request->input('action');

        // Pastikan admin tidak bisa menghapus/blokir dirinya sendiri secara bulk
        $ids = array_diff($ids, [auth()->id()]);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada user yang valid.'], 422);
        }

        try {
            $count = match ($action) {
                'block'   => $this->userService->bulkBlock($ids, $request->input('reason', 'Bulk action'), auth()->id()),
                'unblock' => $this->userService->bulkUnblock($ids, auth()->id()),
                'delete'  => $this->bulkDelete($ids),
            };

            $label = match ($action) {
                'block'   => 'diblokir',
                'unblock' => 'di-unblokir',
                'delete'  => 'dihapus',
            };

            return response()->json([
                'success' => true,
                'message' => "{$count} pengguna berhasil {$label}.",
                'count'   => $count,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Bulk action gagal.'], 500);
        }
    }

    // ─── Private Helpers ───────────────────────────────────────────────────────

    private function bulkDelete(array $ids): int
    {
        $users = User::whereIn('id', $ids)->get();
        foreach ($users as $user) {
            $this->userService->deleteUser($user);
        }
        return $users->count();
    }

    private function formatUser(User $user): array
    {
        $user->loadMissing('activeBlock', 'userBlocks');

        return [
            'id'               => $user->id,
            'name'             => $user->name,
            'email'            => $user->email,
            'is_active'        => $user->is_active,
            'is_blocked'       => $user->is_blocked,
            'block_count'      => $user->userBlocks->count(),
            'block_reason'     => $user->activeBlock?->reason,
            'blocked_until'    => $user->activeBlock?->blocked_until?->toIso8601String(),
            'email_verified'   => !is_null($user->email_verified_at),
            'last_login_at'    => $user->last_login_at?->toIso8601String(),
            'last_login_ip'    => $user->last_login_ip,
            'created_at'       => $user->created_at?->toIso8601String(),
        ];
    }
}
