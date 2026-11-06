<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Game;
use App\Models\GameHistory;
use App\Models\GameSession;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GameController extends Controller
{
    public function random(): RedirectResponse
    {
        $randomGame = Game::published()->inRandomOrder()->first();

        if (!$randomGame) {
            return redirect()->route('games.explore');
        }

        return redirect()->route('games.play', $randomGame->slug);
    }

    public function instantSearch(Request $request)
    {
        $term = trim((string) $request->input('q', ''));
        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $items = Game::published()
            ->with('category')
            ->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%");
            })
            ->orderByDesc('play_count')
            ->limit(6)
            ->get()
            ->map(fn (Game $g) => [
                'title' => $g->title,
                'slug' => $g->slug,
                'url' => route('games.show', $g->slug),
                'thumbnail_url' => $g->thumbnail_url,
                'category' => $g->category->name ?? 'Arcade',
                'play_count' => number_format($g->play_count),
                'avg_rating' => number_format($g->avg_rating, 1),
            ]);

        return response()->json($items);
    }

    public function explore(Request $request): View
    {
        $query = Game::published()->with(['category', 'tags']);

        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            });
        }

        if ($categorySlug = $request->input('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        if ($tagSlug = $request->input('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $tagSlug));
        }

        if ($minRating = (float) $request->input('rating', 0)) {
            $query->where('avg_rating', '>=', $minRating);
        }

        if ($request->boolean('mobile')) {
            $query->where('mobile_support', true);
        }

        $sort = $request->input('sort', 'popular');
        match ($sort) {
            'newest' => $query->latest('published_at'),
            'rating' => $query->orderByDesc('avg_rating')->orderByDesc('review_count'),
            'title' => $query->orderBy('title'),
            default => $query->orderByDesc('play_count'),
        };

        $games = $query->paginate(16)->withQueryString();
        $categories = Category::active()->withCount(['games as published_games_count' => fn ($q) => $q->where('status', 'published')])->get();
        $tags = Tag::active()->get();

        $favoriteIds = auth()->check()
            ? auth()->user()->favorites()->pluck('game_id')->toArray()
            : [];

        return view('games.explore', compact('games', 'categories', 'tags', 'sort', 'favoriteIds'));
    }

    public function category(Request $request, string $slug): View
    {
        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $sort = $request->input('sort', 'popular');
        $query = Game::published()
            ->where('category_id', $category->id)
            ->with(['category', 'tags']);

        match ($sort) {
            'newest' => $query->latest('published_at'),
            'rating' => $query->orderByDesc('avg_rating'),
            default => $query->orderByDesc('play_count'),
        };

        $games = $query->paginate(16)->withQueryString();
        $categories = Category::active()->withCount(['games as published_games_count' => fn ($q) => $q->where('status', 'published')])->get();

        $favoriteIds = auth()->check()
            ? auth()->user()->favorites()->pluck('game_id')->toArray()
            : [];

        return view('games.category', compact('category', 'games', 'categories', 'sort', 'favoriteIds'));
    }

    public function show(string $slug): View
    {
        $game = Game::with([
            'category',
            'tags',
            'currentVersion',
            'approvedReviews.user',
            'scores' => fn ($q) => $q->with('user')->limit(10),
        ])->where('slug', $slug)->firstOrFail();

        if ($game->status !== 'published' && (!auth()->check() || !auth()->user()->isAdmin())) {
            abort(404);
        }

        $relatedGames = Game::published()
            ->where('id', '!=', $game->id)
            ->where('category_id', $game->category_id)
            ->with('category')
            ->orderByDesc('play_count')
            ->limit(6)
            ->get();

        if ($relatedGames->count() < 4) {
            $extra = Game::published()
                ->where('id', '!=', $game->id)
                ->whereNotIn('id', $relatedGames->pluck('id'))
                ->with('category')
                ->orderByDesc('play_count')
                ->limit(6 - $relatedGames->count())
                ->get();
            $relatedGames = $relatedGames->concat($extra);
        }

        $isFavorited = auth()->check()
            && auth()->user()->favorites()->where('game_id', $game->id)->exists();

        $userReview = auth()->check()
            ? $game->reviews()->where('user_id', auth()->id())->first()
            : null;

        return view('games.show', compact('game', 'relatedGames', 'isFavorited', 'userReview'));
    }

    public function play(Request $request, string $slug): View
    {
        $game = Game::with([
            'category',
            'tags',
            'currentVersion',
            'approvedReviews.user',
            'scores' => fn ($q) => $q->with('user')->limit(10),
        ])->where('slug', $slug)->firstOrFail();

        $previewVersionId = $request->integer('version');
        $activeVersion = $game->currentVersion;

        if ($previewVersionId && auth()->check() && auth()->user()->isAdmin()) {
            $activeVersion = $game->versions()->findOrFail($previewVersionId);
        } else {
            if ($game->status !== 'published' || !$activeVersion) {
                if (!auth()->check() || !auth()->user()->isAdmin()) {
                    abort(404, 'Trò chơi này hiện chưa được xuất bản.');
                }
            }
        }

        $game->increment('play_count');

        $sessionToken = Str::uuid()->toString();
        GameSession::create([
            'user_id' => auth()->id(),
            'game_id' => $game->id,
            'game_version_id' => $activeVersion?->id,
            'session_token' => $sessionToken,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'started_at' => now(),
        ]);

        if (auth()->check()) {
            GameHistory::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'game_id' => $game->id,
                ],
                [
                    'played_at' => now(),
                ]
            );
        }

        $relatedGames = Game::published()
            ->where('id', '!=', $game->id)
            ->with('category')
            ->orderByDesc('play_count')
            ->limit(8)
            ->get();

        $isFavorited = auth()->check()
            && auth()->user()->favorites()->where('game_id', $game->id)->exists();

        $userReview = auth()->check()
            ? $game->reviews()->where('user_id', auth()->id())->first()
            : null;

        return view('games.play', compact('game', 'activeVersion', 'sessionToken', 'relatedGames', 'isFavorited', 'userReview'));
    }
}
