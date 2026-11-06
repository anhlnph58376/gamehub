<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Requests\Game\StoreReportRequest;
use App\Http\Requests\Game\StoreReviewRequest;
use App\Models\Achievement;
use App\Models\Game;
use App\Models\GameHistory;
use App\Models\GameReport;
use App\Models\GameReview;
use App\Models\GameScore;
use App\Models\GameSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InteractionController extends Controller
{
    public function storeReview(StoreReviewRequest $request, Game $game): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        $review = GameReview::updateOrCreate(
            [
                'user_id' => $user->id,
                'game_id' => $game->id,
            ],
            [
                'rating' => (int) $request->input('rating'),
                'comment' => $request->input('comment'),
                'status' => 'approved',
            ]
        );

        $game->recalculateRating();
        $game->refresh();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Cảm ơn bạn đã gửi đánh giá cho trò chơi này!',
                'avg_rating' => number_format($game->avg_rating, 1),
                'review_count' => $game->review_count,
                'review' => [
                    'id' => $review->id,
                    'user_name' => $user->name,
                    'user_avatar' => $user->avatar_url,
                    'rating' => $review->rating,
                    'comment' => $review->comment ?: 'Trò chơi rất tuyệt vời!',
                    'time_ago' => 'Vừa xong',
                ],
            ]);
        }

        return back()->with('success', 'Cảm ơn bạn đã gửi đánh giá cho trò chơi này!');
    }

    public function deleteReview(Request $request, GameReview $review): RedirectResponse
    {
        $user = $request->user();

        if (!$user || ($user->id !== $review->user_id && !$user->isAdmin())) {
            abort(403, 'Bạn không có quyền xóa đánh giá này.');
        }

        $game = $review->game;
        $review->delete();

        if ($game) {
            $game->recalculateRating();
        }

        return back()->with('success', 'Đã xóa đánh giá thành công.');
    }

    public function storeReport(StoreReportRequest $request, Game $game): RedirectResponse|JsonResponse
    {
        $report = GameReport::create([
            'user_id' => auth()->id(),
            'game_id' => $game->id,
            'game_review_id' => $request->input('game_review_id'),
            'type' => $request->input('type'),
            'description' => $request->input('description'),
            'status' => 'pending',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Báo cáo của bạn đã được gửi tới Ban quản trị. Cảm ơn sự đóng góp của bạn!',
                'report_id' => $report->id,
            ]);
        }

        return back()->with('success', 'Báo cáo của bạn đã được gửi tới Ban quản trị. Cảm ơn bạn!');
    }

    public function recordSession(Request $request, Game $game): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => ['required', 'string', 'max:100'],
            'duration_seconds' => ['required', 'integer', 'min:1', 'max:86400'],
            'score' => ['nullable', 'integer', 'min:0', 'max:999999999'],
        ]);

        $session = GameSession::where('session_token', $validated['session_token'])
            ->where('game_id', $game->id)
            ->first();

        if ($session) {
            $session->update([
                'ended_at' => now(),
                'duration_seconds' => (int) $validated['duration_seconds'],
            ]);
        } else {
            $session = GameSession::create([
                'user_id' => auth()->id(),
                'game_id' => $game->id,
                'game_version_id' => $game->current_version_id,
                'session_token' => $validated['session_token'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'started_at' => now()->subSeconds((int) $validated['duration_seconds']),
                'ended_at' => now(),
                'duration_seconds' => (int) $validated['duration_seconds'],
            ]);
        }

        $score = (int) ($validated['score'] ?? 0);

        if (auth()->check()) {
            $user = auth()->user();

            GameHistory::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'game_id' => $game->id,
                ],
                [
                    'played_at' => now(),
                    'duration_seconds' => (int) $validated['duration_seconds'],
                    'score' => $score,
                ]
            );

            if ($score > 0) {
                GameScore::create([
                    'game_id' => $game->id,
                    'user_id' => $user->id,
                    'score' => $score,
                    'recorded_at' => now(),
                ]);
            }

            $this->evaluateAchievements($user, $score);
        }

        return response()->json([
            'status' => 'success',
            'session_id' => $session->id,
        ]);
    }

    protected function evaluateAchievements($user, int $latestScore): void
    {
        $gamesPlayed = $user->histories()->count();
        $achievements = Achievement::all();

        foreach ($achievements as $achievement) {
            $unlocked = false;

            if ($achievement->condition_type === 'games_played' && $gamesPlayed >= $achievement->condition_value) {
                $unlocked = true;
            } elseif ($achievement->condition_type === 'high_score' && $latestScore >= $achievement->condition_value) {
                $unlocked = true;
            }

            if ($unlocked && !$user->achievements()->where('achievement_id', $achievement->id)->exists()) {
                $user->achievements()->attach($achievement->id, ['earned_at' => now()]);
                $user->increment('total_points', $achievement->points);
            }
        }
    }
}

