<?php

namespace App\Modules\Common\Services;

class FormattingService
{
    /**
     * Format angka besar jadi 1K, 1JT, 1M, dll secara profesional.
     */
    public function shortNumber(int|float $number): string
    {
        if ($number >= 1_000_000_000) {
            return round($number / 1_000_000_000, 1) . 'B';
        }

        if ($number >= 1_000_000) {
            return round($number / 1_000_000, 1) . 'JT';
        }

        if ($number >= 1_000) {
            $formatted = round($number / 1_000, 1);
            // Hilangkan .0 jika bulat
            if (floor($formatted) == $formatted) {
                $formatted = floor($formatted);
            }
            return $formatted . 'K';
        }

        return number_format($number);
    }

    /**
     * Format byte ke satuan yang mudah dibaca.
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Format ke mata uang (default IDR).
     */
    public function formatMoney(int|float $amount, string $currency = 'IDR'): string
    {
        if ($currency === 'IDR') {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        }

        return $currency . ' ' . number_format($amount, 2);
    }

    /**
     * Format ke persentase.
     */
    public function formatPercent(float $value, int $precision = 1): string
    {
        return round($value, $precision) . '%';
    }
}
