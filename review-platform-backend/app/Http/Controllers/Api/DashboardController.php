<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $statsService;

    public function __construct(StatisticsService $statsService)
    {
        $this->statsService = $statsService;
    }

    /**
     * Get dashboard statistics
     */
    public function stats()
    {
        $statistics = $this->statsService->getStatistics();
        
        return response()->json($statistics);
    }
}
