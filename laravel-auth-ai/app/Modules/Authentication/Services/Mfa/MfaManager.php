<?php

namespace App\Modules\Authentication\Services\Mfa;

use App\Models\User;
use App\Modules\Authentication\Services\Mfa\Contracts\MfaStrategyInterface;
use App\Modules\Authentication\Services\Mfa\Strategies\EmailMfaStrategy;
use App\Modules\Authentication\Services\Mfa\Strategies\TotpMfaStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class MfaManager
{
    /** @var Collection<string, MfaStrategyInterface> */
    private Collection $strategies;

    public function __construct(
        EmailMfaStrategy $emailMfa,
        TotpMfaStrategy $totpMfa
    ) {
        $this->strategies = collect([
            $emailMfa->identifier() => $emailMfa,
            $totpMfa->identifier()  => $totpMfa,
        ]);
    }

    /**
     * Dapatkan strategi aktif untuk user.
     */
    public function getStrategyForUser(User $user): MfaStrategyInterface
    {
        $type = $user->mfa_type ?? 'email';

        // Fallback ke email jika TOTP diminta tapi belum setup
        if ($type === 'totp' && empty($user->totp_secret)) {
            $type = 'email';
        }

        return $this->getStrategy($type);
    }

    /**
     * Jalankan inisialisasi MFA (generate code/session).
     */
    public function initiate(User $user, Request $request, ?int $logId = null): array
    {
        return $this->getStrategyForUser($user)->generate($user, $request, $logId);
    }

    /**
     * Verifikasi kode MFA.
     */
    public function verify(User $user, string $code, string $sessionToken): bool
    {
        return $this->getStrategyForUser($user)->verify($user, $code, $sessionToken);
    }

    /**
     * Ambil strategi berdasarkan identifier.
     */
    public function getStrategy(string $type): MfaStrategyInterface
    {
        $strategy = $this->strategies->get($type);

        if (!$strategy) {
            throw new InvalidArgumentException("MFA Strategy [{$type}] tidak terdaftar.");
        }

        return $strategy;
    }
}
