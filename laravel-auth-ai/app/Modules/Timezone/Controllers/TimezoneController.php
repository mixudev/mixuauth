<?php

namespace App\Modules\Timezone\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Timezone\Services\TimezoneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimezoneController extends Controller
{
    public function __construct(
        private readonly TimezoneService $timezoneService
    ) {}

    /**
     * Set timezone dari AJAX browser.
     *
     * PERBAIKAN dari versi sebelumnya:
     * - Jika user SUDAH login → langsung simpan ke DB (bukan hanya session)
     * - Timezone diambil dari body JSON atau header X-Timezone
     */
    public function set(Request $request): JsonResponse
    {
        // Ambil timezone dari body, fallback ke header
        $timezone = $request->input('timezone')
            ?? $request->header('X-Timezone')
            ?? '';

        if (! $this->timezoneService->isValid($timezone)) {
            return response()->json([
                'success' => false,
                'message' => 'Timezone tidak valid.',
            ], 422);
        }

        if (auth()->check()) {
            // User login → simpan ke DB sekaligus session
            $this->timezoneService->saveUserTimezone($timezone);
        } else {
            // Guest → simpan ke session saja (DB belum bisa, belum login)
            $this->timezoneService->setUserTimezone($timezone);
        }

        return response()->json([
            'success'  => true,
            'timezone' => $timezone,
        ]);
    }

    /**
     * Update timezone dari halaman profil (form manual oleh user).
     */
    public function update(Request $request)
    {
        $request->validate([
            'timezone' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    if (! $this->timezoneService->isValid($value)) {
                        $fail('Timezone yang dipilih tidak valid.');
                    }
                },
            ],
        ]);

        $this->timezoneService->saveUserTimezone($request->timezone);

        return back()->with('success', 'Timezone berhasil disimpan.');
    }
}
