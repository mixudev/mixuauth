<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetService
{
    private const TABLE          = 'password_reset_tokens';
    private const TOKEN_LENGTH   = 64;
    private const EXPIRE_MINUTES = 60;

    /**
     * Membuat token reset password baru.
     */
    public function createToken(string $email): string
    {
        $plainToken = Str::random(self::TOKEN_LENGTH);

        // Gunakan updateOrInsert agar 1 email hanya punya 1 token aktif yang valid
        DB::table(self::TABLE)->updateOrInsert(
            ['email' => $email],
            [
                'token'      => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        return $plainToken;
    }

    /**
     * Memvalidasi token reset password.
     */
    public function validateToken(string $email, string $plainToken): array
    {
        $record = DB::table(self::TABLE)->where('email', $email)->first();

        // Cek kecocokan hash token
        if (! $record) {
            return ['success' => false, 'reason' => 'invalid_token'];
        }

        /** @var \stdClass $record */
        if (! Hash::check($plainToken, $record->token)) {
            return ['success' => false, 'reason' => 'invalid_token'];
        }

        // Cek kedaluwarsa
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(self::EXPIRE_MINUTES)->isPast()) {
            return ['success' => false, 'reason' => 'expired'];
        }

        return ['success' => true];
    }

    /**
     * Menghapus token setelah digunakan atau jika ingin membatalkan.
     */
    public function deleteToken(string $email): void
    {
        DB::table(self::TABLE)->where('email', $email)->delete();
    }
}
