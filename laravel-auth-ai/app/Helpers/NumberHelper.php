<?php

if (! function_exists('short_number')) {
    /**
     * Format angka besar jadi 1K, 1JT, 1M, dll
     */
    function short_number(int|float $number): string
    {
        if ($number >= 1_000_000_000) {
            return round($number / 1_000_000_000, 1) . 'B';
        }

        if ($number >= 1_000_000) {
            return round($number / 1_000_000, 1) . 'JT';
        }

        if ($number >= 1_000) {
            return round($number / 1_000, 1) . 'K';
        }

        return (string) $number;
    }
}