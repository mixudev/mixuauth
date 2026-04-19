<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Services\DashboardStatsService;
use App\Modules\Dashboard\Services\DashboardChartService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardStatsService $statsService,
        private readonly DashboardChartService $chartService,
    ) {}

    public function index(Request $request)
    {
        $period = $request->query('period', '7d');

        // ── Stat Cards ────────────────────────────────────────────────────────
        $stats               = $this->statsService->getCardStats($period);
        $otpSummary          = $this->statsService->getOtpSummary();
        $deviceSummary       = $this->statsService->getDeviceSummary();
        $unreadNotifications = $this->statsService->getUnreadNotificationCount();
        $criticalAlerts      = $this->statsService->getCriticalAlerts();

        // ── Table Data ────────────────────────────────────────────────────────
        $recentLogs   = $this->statsService->getRecentLogs();
        $topThreatIps = $this->statsService->getTopThreatIps($period)?->take(6);
        $recentNotifs = $this->statsService->getRecentNotifications();

        // ── Chart Data ────────────────────────────────────────────────────────
        $chartData     = $this->chartService->getLoginActivityChart($period);
        $riskChartData = $this->chartService->getRiskScoreChart($period);
        $todayHourly   = $this->chartService->getTodayHourlyStats();

        $decisionBreakdown = array_merge(
            [
                'ALLOW'    => 0,
                'OTP'      => 0,
                'BLOCK'    => 0,
                'FALLBACK' => 0,
                'PENDING'  => 0,
            ],
            $this->statsService->getDecisionBreakdown($period)
        );

        return view('dashboard::index', [
            'currentPeriod'       => $period,
            'stats'               => $stats,
            'decisionBreakdown'   => $decisionBreakdown,
            'otpSummary'          => $otpSummary,
            'deviceSummary'       => $deviceSummary,
            'unreadNotifications' => $unreadNotifications,
            'criticalAlerts'      => $criticalAlerts,
            'recentLogs'          => $recentLogs,
            'topThreatIps'        => $topThreatIps,
            'recentNotifs'        => $recentNotifs,

            // Chart.js datasets — period-based (di-embed via @json)
            'chartLabels'         => $chartData['labels'],
            'chartSuccess'        => $chartData['success'],
            'chartOtp'            => $chartData['otp'],
            'chartBlocked'        => $chartData['blocked'],
            'chartFailed'         => $chartData['failed'],
            'riskAvg'             => $riskChartData['avg'],
            'riskMax'             => $riskChartData['max'],
            'decisionCounts'      => $decisionBreakdown,

            // Chart.js datasets — hari ini per jam (tidak terpengaruh period selector)
            'todaySuccessHourly'  => $todayHourly['success'],
            'todayOtpHourly'      => $todayHourly['otp'],
            'todayFailedHourly'   => $todayHourly['failed'],
            'todayBlockedHourly'  => $todayHourly['blocked'],
        ]);
    }
}
