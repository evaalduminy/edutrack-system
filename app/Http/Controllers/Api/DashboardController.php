<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ResearchService;
use App\Services\ArchiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Dashboard Controller (API)
 *
 * Provides cached statistics for the dashboard.
 * All data is cached in Redis/database to reduce DB pressure.
 *
 * @group Dashboard
 * @authenticated
 */
class DashboardController extends Controller
{
    public function __construct(
        protected ResearchService $researchService,
        protected ArchiveService $archiveService,
    ) {}

    /**
     * Get dashboard statistics.
     *
     * Returns cached statistics about research and archives.
     * Cache TTL: 15 minutes.
     *
     * @response 200 {"data": {"research": {...}, "archive": {...}}}
     */
    public function index(): JsonResponse
    {
        $stats = Cache::remember('dashboard_statistics', now()->addMinutes(15), function () {
            return [
                'research' => $this->researchService->getStatistics(),
                'archive'  => $this->archiveService->getStatistics(),
            ];
        });

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Force refresh dashboard statistics cache.
     *
     * @response 200 {"message": "تم تحديث الإحصائيات.", "data": {...}}
     */
    public function refresh(): JsonResponse
    {
        Cache::forget('dashboard_statistics');
        Cache::forget('research_statistics');
        Cache::forget('archive_statistics');

        $stats = [
            'research' => $this->researchService->getStatistics(),
            'archive'  => $this->archiveService->getStatistics(),
        ];

        Cache::put('dashboard_statistics', $stats, now()->addMinutes(15));

        return response()->json([
            'message' => 'تم تحديث الإحصائيات.',
            'data'    => $stats,
        ]);
    }
}
