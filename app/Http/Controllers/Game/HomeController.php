<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Game;
use App\Models\HomepageSection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $banners = Cache::remember('home_banners', 300, function () {
            return Banner::active()->with('game.category')->get();
        });

        $announcements = Announcement::current()->latest()->get();

        $sections = HomepageSection::active()->get()->keyBy('slug');

        $featuredGames = Game::published()
            ->where('is_featured', true)
            ->with(['category', 'tags'])
            ->orderByDesc('play_count')
            ->limit(8)
            ->get();

        if ($featuredGames->isEmpty()) {
            $featuredGames = Game::published()
                ->with(['category', 'tags'])
                ->orderByDesc('play_count')
                ->limit(8)
                ->get();
        }

        $popularGames = Game::published()
            ->with(['category', 'tags'])
            ->orderByDesc('play_count')
            ->limit(8)
            ->get();

        $newestGames = Game::published()
            ->with(['category', 'tags'])
            ->latest('published_at')
            ->limit(8)
            ->get();

        $topRatedGames = Game::published()
            ->with(['category', 'tags'])
            ->orderByDesc('avg_rating')
            ->orderByDesc('review_count')
            ->limit(8)
            ->get();

        $mostPlayedGames = Game::published()
            ->with(['category', 'tags'])
            ->orderByDesc('play_count')
            ->limit(6)
            ->get();

        $recommendedGames = Game::published()
            ->with(['category', 'tags'])
            ->inRandomOrder()
            ->limit(8)
            ->get();

        $categories = Category::active()
            ->withCount(['games as published_games_count' => fn ($q) => $q->where('status', 'published')])
            ->get();

        $recentHistories = auth()->check()
            ? auth()->user()->histories()->with('game.category')->limit(6)->get()
            : collect();

        $favoriteIds = auth()->check()
            ? auth()->user()->favorites()->pluck('game_id')->toArray()
            : [];

        return view('games.home', compact(
            'banners',
            'announcements',
            'sections',
            'featuredGames',
            'popularGames',
            'newestGames',
            'topRatedGames',
            'mostPlayedGames',
            'recommendedGames',
            'categories',
            'recentHistories',
            'favoriteIds'
        ));
    }
}

