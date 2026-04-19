<?php

namespace App\Shared\Contracts;

use App\Shared\DTO\RiskAssessmentResult;

interface RiskAssessorInterface
{
    /**
     * Menilai risiko dari payload dan mengembalikan hasil penilaian.
     *
     * @param array<string, mixed> $payload
     * @return RiskAssessmentResult
     * @throws \RuntimeException
     */
    public function assess(array $payload): RiskAssessmentResult;
}
