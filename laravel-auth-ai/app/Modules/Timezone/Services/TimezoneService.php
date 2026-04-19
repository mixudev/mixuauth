<?php

namespace App\Modules\Timezone\Services;

use Carbon\Carbon;
use DateTimeZone;
use InvalidArgumentException;

class TimezoneService
{
    public const DEFAULT_TIMEZONE = 'UTC';

    /**
     * Ambil timezone aktif user.
     * Prioritas: session → kolom DB (jika login) → default UTC
     */
    public function getUserTimezone(): string
    {
        // Prioritas 1: sudah di-set di session request ini
        if (session()->has('user_timezone')) {
            return session('user_timezone');
        }

        // Prioritas 2: ambil dari kolom timezone di tabel users
        if (auth()->check()) {
            $tz = auth()->user()->timezone ?? null;

            if ($tz && $this->isValid($tz)) {
                // Cache ke session agar tidak query DB setiap request
                session(['user_timezone' => $tz]);
                return $tz;
            }
        }

        // Prioritas 3: fallback ke UTC
        return config('app.timezone', self::DEFAULT_TIMEZONE);
    }

    /**
     * Set timezone user ke session (untuk guest / request saat ini).
     */
    public function setUserTimezone(string $timezone): void
    {
        if (! $this->isValid($timezone)) {
            throw new InvalidArgumentException("Timezone tidak valid: {$timezone}");
        }

        session(['user_timezone' => $timezone]);
    }

    /**
     * Simpan timezone ke session DAN ke kolom DB (untuk user yang login).
     * Gunakan ini saat user mengubah preferensi timezone di halaman profil.
     */
    public function saveUserTimezone(string $timezone): void
    {
        if (! $this->isValid($timezone)) {
            throw new InvalidArgumentException("Timezone tidak valid: {$timezone}");
        }

        // Simpan ke session
        session(['user_timezone' => $timezone]);

        // Simpan ke database jika user sedang login
        if (auth()->check()) {
            auth()->user()->update(['timezone' => $timezone]);
        }
    }

    /**
     * Validasi apakah timezone adalah IANA timezone yang valid.
     * Menggunakan whitelist resmi — aman dari injection.
     */
    public function isValid(string $timezone): bool
    {
        // Panjang timezone IANA tidak pernah melebihi 50 karakter
        if (strlen($timezone) > 50) {
            return false;
        }

        return in_array($timezone, DateTimeZone::listIdentifiers(), true);
    }

    /**
     * Konversi Carbon UTC ke timezone lokal user, kembalikan objek Carbon.
     * Gunakan ini jika butuh manipulasi Carbon lebih lanjut.
     */
    public function toLocal(Carbon $date, ?string $timezone = null): Carbon
    {
        return $date->copy()->setTimezone($timezone ?? $this->getUserTimezone());
    }

    /**
     * Konversi Carbon ke UTC (untuk disimpan ke DB).
     */
    public function toUtc(Carbon $date): Carbon
    {
        return $date->copy()->utc();
    }

    /**
     * Format Carbon ke string waktu lokal user.
     *
     * @param Carbon      $date     Objek Carbon (biasanya dari model Eloquent)
     * @param string      $format   Format tanggal (default: 'd M Y, H:i')
     * @param string|null $timezone Override timezone (opsional)
     */
    public function format(Carbon $date, string $format = 'd M Y, H:i', ?string $timezone = null): string
    {
        return $this->toLocal($date, $timezone)->translatedFormat($format);
    }

    /**
     * Tampilkan waktu relative ("2 menit yang lalu") dalam timezone lokal user.
     */
    public function diffForHumans(Carbon $date, ?string $timezone = null): string
    {
        return $this->toLocal($date, $timezone)->diffForHumans();
    }

    /**
     * Kembalikan semua timezone IANA yang valid.
     * Berguna untuk dropdown pilihan timezone di halaman profil.
     */
    public function allTimezones(): array
    {
        return DateTimeZone::listIdentifiers();
    }

    /**
     * Kembalikan timezone yang dikelompokkan per region.
     * Berguna untuk dropdown yang lebih rapi (grouped <optgroup>).
     */
    public function groupedTimezones(): array
    {
        $grouped = [];

        foreach (DateTimeZone::listIdentifiers() as $tz) {
            $parts = explode('/', $tz, 2);
            $region = $parts[0];
            $grouped[$region][] = $tz;
        }

        ksort($grouped);

        return $grouped;
    }
}
