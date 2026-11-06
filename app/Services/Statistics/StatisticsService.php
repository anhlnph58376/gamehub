<?php

namespace App\Services\Statistics;

use App\Models\Category;
use App\Models\Game;
use App\Models\GameReport;
use App\Models\GameReview;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Support\Carbon;

class StatisticsService
{
    public function getDashboardMetrics(): array
    {
        $totalGames = Game::withTrashed()->count();
        $publishedGames = Game::where('status', 'published')->count();
        $totalUsers = User::count();
        $totalPlays = (int) Game::sum('play_count');
        $totalCategories = Category::count();
        $totalReviews = GameReview::count();
        $pendingReports = GameReport::where('status', 'pending')->count();

        $dailyPlays = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = GameSession::whereDate('started_at', $date)->count();
            $dailyPlays[] = [
                'date' => $date->format('d/m'),
                'label' => $date->translatedFormat('D, d/m'),
                'count' => $count,
            ];
        }

        $topGames = Game::with('category')
            ->orderByDesc('play_count')
            ->limit(6)
            ->get();

        $newestUsers = User::with('roles')
            ->latest()
            ->limit(6)
            ->get();

        return [
            'total_games' => $totalGames,
            'published_games' => $publishedGames,
            'total_users' => $totalUsers,
            'total_plays' => $totalPlays,
            'total_categories' => $totalCategories,
            'total_reviews' => $totalReviews,
            'pending_reports' => $pendingReports,
            'daily_plays' => $dailyPlays,
            'top_games' => $topGames,
            'newest_users' => $newestUsers,
        ];
    }
}

