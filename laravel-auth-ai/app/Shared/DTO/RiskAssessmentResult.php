<?php

namespace App\Shared\DTO;

/**
 * Data Transfer Object untuk hasil penilaian risiko dari layanan AI.
 * Digunakan sebagai kontrak antar layanan, bukan array biasa.
 */
readonly class RiskAssessmentResult
{
    public function __construct(
        public int    $riskScore,
        public string $decision,      // ALLOW | OTP | BLOCK
        public array  $reasonFlags,   // Alasan-alasan yang dapat dijelaskan
        public array  $rawResponse,   // Respons mentah untuk keperluan audit
        public array  $payload = [],  // Input yang dikirim ke AI (untuk training)
        public bool   $isFallback = false, // Apakah skor ini dari rule-based fallback
    ) {}

    /**
     * Periksa apakah keputusan adalah ALLOW.
     */
    public function isAllowed(): bool
    {
        return $this->decision === 'ALLOW';
    }

    /**
     * Periksa apakah keputusan memerlukan OTP.
     */
    public function requiresOtp(): bool
    {
        return $this->decision === 'OTP';
    }

    /**
     * Periksa apakah login harus diblokir.
     */
    public function isBlocked(): bool
    {
        return $this->decision === 'BLOCK';
    }
}
