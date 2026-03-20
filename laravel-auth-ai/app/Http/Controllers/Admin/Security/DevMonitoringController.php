<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;

/**
 * DEV ONLY — Remove or protect this controller before deploying to production!
 *
 * Acts as a thin router. All domain logic lives in dedicated sub-controllers.
 *
 * Sub-controllers:
 *   DevStatsController       – aggregate statistics
 *   DevOtpController         – OTP verification records
 *   DevLoginLogController    – login attempt logs + CSV export
 *   DevTrustedDeviceController – trusted device management
 *   DevUserController        – user listing + block/unblock
 *   DevIpBlacklistController – IP blacklist CRUD
 *   DevIpWhitelistController – IP whitelist CRUD
 */
class DevMonitoringController extends Controller
{
    public function dashboard(): \Illuminate\View\View
    {
        return view('layouts.app-scurity');
    }
}
