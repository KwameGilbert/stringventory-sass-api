<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helper\ResponseHelper;
use App\Models\Business;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\AuditLog;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;
use Illuminate\Database\Capsule\Manager as DB;

class AdminController
{
    /**
     * Get system health and diagnostics status
     */
    public function systemHealth(Request $request, Response $response): Response
    {
        try {
            $dbConnected = false;
            try {
                DB::connection()->getPdo();
                $dbConnected = true;
            } catch (Exception $dbEx) {
                // DB connection failed
            }

            $diagnostics = [
                'status' => $dbConnected ? 'healthy' : 'unhealthy',
                'phpVersion' => PHP_VERSION,
                'os' => PHP_OS,
                'environment' => $_ENV['APP_ENV'] ?? 'production',
                'database' => [
                    'connected' => $dbConnected,
                    'driver' => DB::connection()->getConfig('driver') ?? 'mysql',
                ],
                'memoryUsage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
                'peakMemoryUsage' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB',
                'timestamp' => date('Y-m-d H:i:s'),
            ];

            return ResponseHelper::success($response, 'System health diagnostics fetched successfully', $diagnostics);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch system diagnostics', 500, $e->getMessage());
        }
    }

    /**
     * Get master metrics dashboard for platform administrators
     */
    public function metrics(Request $request, Response $response): Response
    {
        try {
            $totalBusinesses = Business::count();
            $totalUsers = User::count();
            $activeSubscriptions = Subscription::where('status', 'active')->count();

            $metrics = [
                'businessesCount' => $totalBusinesses,
                'usersCount' => $totalUsers,
                'activeSubscriptionsCount' => $activeSubscriptions,
                'systemUptime' => '99.99%',
            ];

            return ResponseHelper::success($response, 'Platform admin metrics fetched successfully', $metrics);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch platform metrics', 500, $e->getMessage());
        }
    }

    /**
     * Get platform analytics for Superadmin dashboard
     */
    public function getPlatformAnalytics(Request $request, Response $response): Response
    {
        try {
            $totalBusinesses = Business::count();
            $activeSubscriptions = Subscription::where('status', 'active')->count();
            $totalUsers = User::count();
            
            // Calculate Monthly Recurring Revenue
            $mrr = Subscription::where('status', 'active')
                ->join('plans', 'subscriptions.planId', '=', 'plans.id')
                ->sum('plans.priceMonthly');
                
            $mrr = (float) $mrr;

            // Simplified plan distribution
            $planDistribution = [];
            $plans = Plan::all();
            foreach ($plans as $plan) {
                $subscribersCount = Subscription::where('planId', $plan->id)->where('status', 'active')->count();
                $revenue = $subscribersCount * $plan->priceMonthly;
                $planDistribution[] = [
                    'plan' => $plan->name,
                    'count' => $subscribersCount,
                    'percentage' => $activeSubscriptions > 0 ? round(($subscribersCount / $activeSubscriptions) * 100, 1) : 0,
                    'revenue' => $revenue,
                    'color' => $plan->color ?? '#3b82f6'
                ];
            }

            // Simplified recent activity
            $recentActivityLogs = AuditLog::orderBy('createdAt', 'desc')->limit(10)->get();
            $recentActivity = [];
            foreach ($recentActivityLogs as $log) {
                $recentActivity[] = [
                    'id' => $log->id,
                    'type' => $log->action,
                    'business' => $log->businessId,
                    'plan' => null,
                    'amount' => 0,
                    'time' => Carbon::parse($log->createdAt)->diffForHumans()
                ];
            }

            $data = [
                'totalBusinesses' => $totalBusinesses,
                'activeSubscriptions' => $activeSubscriptions,
                'monthlyRecurringRevenue' => $mrr,
                'totalUsers' => $totalUsers,
                'businessesChange' => 0, // Mocked trends for now
                'subscriptionsChange' => 0,
                'mrrChange' => 0,
                'usersChange' => 0,
                'churnRate' => 0,
                'totalSubscribers' => $activeSubscriptions,
                'totalMRR' => $mrr,
                'revenueTrends' => [], // Needs more complex date aggregation
                'planDistribution' => $planDistribution,
                'revenueByPlan' => $planDistribution,
                'recentActivity' => $recentActivity,
                'topBusinesses' => [],
                'userGrowth' => [],
                'geographicDistribution' => [],
                'planStats' => [],
            ];

            return ResponseHelper::jsonResponse($response, [
                'success' => true,
                'currency' => 'USD',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch platform analytics', 500, $e->getMessage());
        }
    }
}
