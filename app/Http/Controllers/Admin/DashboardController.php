<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Statistics\StatisticsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected StatisticsService $statisticsService)
    {
    }

    public function index(): View
    {
        $metrics = $this->statisticsService->getDashboardMetrics();

        return view('admin.dashboard', compact('metrics'));
    }
}

