<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Models\Game;
use App\Models\GameFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load([
            'roles',
            'achievements',
            'favoriteGames.category',
            'histories' => fn ($q) => $q->with('game.category')->limit(12),
            'reviews.game',
        ]);

        $stats = [
            'games_played' => $user->histories()->distinct('game_id')->count('game_id'),
            'total_sessions' => $user->histories()->count(),
            'total_duration_minutes' => (int) ceil($user->histories()->sum('duration_seconds') / 60),
            'favorites_count' => $user->favorites()->count(),
            'reviews_count' => $user->reviews()->count(),
            'achievements_count' => $user->achievements->count(),
        ];

        return view('user.profile', compact('user', 'stats'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'bio' => $request->input('bio'),
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $user->update($data);

        return back()->with('success', 'Hồ sơ cá nhân đã được cập nhật thành công.');
    }

    public function favorites(Request $request): View
    {
        $favorites = $request->user()
            ->favoriteGames()
            ->with('category')
            ->paginate(12);

        return view('user.favorites', compact('favorites'));
    }

    public function history(Request $request): View
    {
        $histories = $request->user()
            ->histories()
            ->with('game.category')
            ->paginate(15);

        return view('user.history', compact('histories'));
    }

    public function toggleFavorite(Request $request, Game $game): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        $existing = GameFavorite::where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $isFavorited = false;
            $message = 'Đã xóa game khỏi danh sách Yêu thích.';
        } else {
            GameFavorite::create([
                'user_id' => $user->id,
                'game_id' => $game->id,
            ]);
            $isFavorited = true;
            $message = 'Đã thêm game vào danh sách Yêu thích!';
        }

        $game->update([
            'favorite_count' => GameFavorite::where('game_id', $game->id)->count(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'favorited' => $isFavorited,
                'favorite_count' => $game->favorite_count,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}

