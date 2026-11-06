<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\GameReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    public function index(Request $request): View
    {
        $query = GameReview::with(['user', 'game']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $reviews = $query->latest()->paginate(15)->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function updateStatus(Request $request, GameReview $review): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,hidden'],
        ]);

        $review->update(['status' => $validated['status']]);
        $review->game?->recalculateRating();

        AuditLog::record('review.update_status', "Cập nhật trạng thái đánh giá #{$review->id} sang {$validated['status']}.", GameReview::class, $review->id);

        return back()->with('success', 'Đã cập nhật trạng thái đánh giá thành công.');
    }

    public function destroy(GameReview $review): RedirectResponse
    {
        $game = $review->game;
        $id = $review->id;
        $review->delete();
        $game?->recalculateRating();

        AuditLog::record('review.delete', "Xóa đánh giá #{$id}.", GameReview::class, $id);

        return back()->with('success', 'Đã xóa đánh giá vi phạm.');
    }
}

