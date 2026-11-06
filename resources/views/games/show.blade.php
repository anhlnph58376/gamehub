@extends('layouts.app')

@php
    $title = $game->title;
    $metaDescription = $game->short_description ?: Str::limit($game->description, 160);
    $metaImage = $game->thumbnail_url;
@endphp

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10" x-data="{ reportModalOpen: false, reportReviewId: null }">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-[#A5ABC0]">
        <a href="{{ route('home') }}" class="hover:text-white">Trang chủ</a>
        <span>/</span>
        @if($game->category)
            <a href="{{ route('games.category', $game->category->slug) }}" class="hover:text-white">{{ $game->category->name }}</a>
            <span>/</span>
        @endif
        <span class="text-white font-semibold">{{ $game->title }}</span>
    </nav>

    <!-- Hero Game Info Card -->
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-6 sm:p-8 lg:p-10 shadow-2xl">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <!-- Thumbnail Preview -->
            <div class="lg:col-span-5">
                <div class="relative rounded-2xl overflow-hidden aspect-video bg-[#10121B] border border-white/10 shadow-xl">
                    <img src="{{ $game->thumbnail_url }}" alt="{{ $game->title }}" class="w-full h-full object-cover">
                    <div class="absolute top-3 left-3 flex gap-2">
                        @if($game->is_featured)
                            <span class="px-2.5 py-1 rounded-lg bg-[#7C5CFF] text-white text-xs font-extrabold uppercase">Featured</span>
                        @endif
                        @if($game->is_hot)
                            <span class="px-2.5 py-1 rounded-lg bg-rose-500 text-white text-xs font-extrabold uppercase">Hot</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Game Metadata & Actions -->
            <div class="lg:col-span-7 space-y-5">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('games.category', $game->category->slug) }}" class="px-3 py-1 rounded-xl bg-[#7C5CFF]/20 border border-[#7C5CFF]/40 text-xs font-bold text-[#00D4FF]">
                        {{ $game->category->name ?? 'Arcade' }}
                    </a>
                    @foreach($game->tags as $tag)
                        <a href="{{ route('games.explore', ['tag' => $tag->slug]) }}" class="px-2.5 py-1 rounded-lg bg-[#202437] text-xs text-[#A5ABC0] hover:text-white">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>

                <h1 class="text-2xl sm:text-4xl font-black text-white">{{ $game->title }}</h1>

                <p class="text-sm sm:text-base text-[#A5ABC0] leading-relaxed">
                    {{ $game->short_description ?: $game->description }}
                </p>

                <!-- Key Metrics -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                    <div class="bg-[#202437] rounded-2xl p-3.5 border border-white/5">
                        <p class="text-xs text-[#A5ABC0]">Điểm đánh giá</p>
                        <p class="text-lg font-black text-[#FFB84D] mt-0.5">★ {{ number_format($game->avg_rating, 1) }} <span class="text-xs font-normal text-[#A5ABC0]">({{ $game->review_count }})</span></p>
                    </div>
                    <div class="bg-[#202437] rounded-2xl p-3.5 border border-white/5">
                        <p class="text-xs text-[#A5ABC0]">Lượt chơi</p>
                        <p class="text-lg font-black text-white mt-0.5">{{ number_format($game->play_count) }}</p>
                    </div>
                    <div class="bg-[#202437] rounded-2xl p-3.5 border border-white/5">
                        <p class="text-xs text-[#A5ABC0]">Nhà phát triển</p>
                        <p class="text-sm font-bold text-[#00D4FF] mt-1 truncate">{{ $game->author ?: 'GameHub Studio' }}</p>
                    </div>
                    <div class="bg-[#202437] rounded-2xl p-3.5 border border-white/5">
                        <p class="text-xs text-[#A5ABC0]">Thiết bị hỗ trợ</p>
                        <p class="text-sm font-bold text-emerald-400 mt-1">{{ $game->mobile_support ? '📱 PC & Mobile' : '💻 Máy tính PC' }}</p>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-wrap items-center gap-3 pt-3">
                    <a href="{{ route('games.play', $game->slug) }}" class="px-8 py-4 rounded-2xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white font-black text-base shadow-xl shadow-[#7C5CFF]/30 hover:scale-105 transition flex items-center gap-2">
                        <span>▶ CHƠI NGAY</span>
                    </a>

                    @auth
                        <form action="{{ route('games.favorite.toggle', $game) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-5 py-4 rounded-2xl border transition font-bold text-sm flex items-center gap-2 {{ $isFavorited ? 'bg-rose-500/20 border-rose-500/50 text-rose-400' : 'bg-[#202437] border-white/10 text-white hover:bg-[#282d45]' }}">
                                <span>{{ $isFavorited ? '❤️ Đã Yêu Thích' : '🤍 Yêu Thích' }}</span>
                                <span class="text-xs opacity-80">({{ $game->favorite_count }})</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-4 rounded-2xl bg-[#202437] border border-white/10 text-white font-bold text-sm hover:bg-[#282d45]">
                            🤍 Yêu thích ({{ $game->favorite_count }})
                        </a>
                    @endauth

                    <button type="button" @click="navigator.clipboard.writeText(window.location.href); alert('Đã sao chép liên kết trò chơi!');" class="px-4 py-4 rounded-2xl bg-[#202437] border border-white/10 text-[#A5ABC0] hover:text-white text-sm font-bold">
                        🔗 Chia sẻ
                    </button>

                    <button type="button" @click="reportReviewId = null; reportModalOpen = true" class="px-4 py-4 rounded-2xl bg-[#202437] border border-white/10 text-rose-400 hover:bg-rose-500/10 text-sm font-bold">
                        🚩 Báo lỗi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions, Controls & High Scores -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-[#181B28] border border-white/10 rounded-3xl p-6 sm:p-8 space-y-5">
                <h2 class="text-xl font-extrabold text-white">📖 Giới Thiệu & Hướng Dẫn Chơi</h2>
                <div class="text-sm sm:text-base text-[#A5ABC0] leading-relaxed space-y-3">
                    <p>{{ $game->description }}</p>
                    @if($game->instructions)
                        <div class="p-4 rounded-2xl bg-[#202437] border border-white/5 text-white">
                            <h3 class="font-bold text-sm text-[#00D4FF] mb-1">🎯 Cách chơi & Mục tiêu:</h3>
                            <p class="text-sm text-[#A5ABC0]">{{ $game->instructions }}</p>
                        </div>
                    @endif
                </div>

                <h3 class="text-base font-extrabold text-white pt-2">🎮 Điều Khiển Trong Game</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-[#202437] rounded-2xl p-4 border border-white/5 space-y-2">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-extrabold text-[#00D4FF] uppercase">⌨️ Bàn phím</p>
                            <div class="flex items-center gap-1">
                                <kbd class="kbd-key">W</kbd>
                                <kbd class="kbd-key">A</kbd>
                                <kbd class="kbd-key">S</kbd>
                                <kbd class="kbd-key">D</kbd>
                                <kbd class="kbd-key">Space</kbd>
                            </div>
                        </div>
                        <p class="text-sm text-white">{{ $game->controls['keyboard'] ?? 'Phím mũi tên / WASD / Spacebar' }}</p>
                    </div>
                    <div class="bg-[#202437] rounded-2xl p-4 border border-white/5 space-y-2">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-extrabold text-[#FFB84D] uppercase">🖱️ Chuột</p>
                            <kbd class="kbd-key">🖱️ Chuột trái</kbd>
                        </div>
                        <p class="text-sm text-white">{{ $game->controls['mouse'] ?? 'Click chuột trái để tương tác' }}</p>
                    </div>
                    <div class="bg-[#202437] rounded-2xl p-4 border border-white/5 space-y-2">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-extrabold text-emerald-400 uppercase">📱 Cảm ứng Mobile</p>
                            <kbd class="kbd-key">👆 Chạm</kbd>
                        </div>
                        <p class="text-sm text-white">{{ $game->controls['touch'] ?? 'Chạm / Vuốt trực tiếp trên màn hình' }}</p>
                    </div>
                </div>
            </div>

            <!-- Reviews & Ratings Section -->
            <div class="bg-[#181B28] border border-white/10 rounded-3xl p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-extrabold text-white">⭐ Đánh Giá Từ Người Chơi ({{ $game->approvedReviews->count() }})</h2>
                    <span class="text-lg font-black text-[#FFB84D]">★ {{ number_format($game->avg_rating, 1) }} / 5.0</span>
                </div>

                @auth
                    <form action="{{ route('games.reviews.store', $game) }}" method="POST" class="bg-[#202437] rounded-2xl p-5 border border-white/10 space-y-4">
                        @csrf
                        <h3 class="text-sm font-bold text-white">
                            {{ $userReview ? 'Cập nhật đánh giá của bạn' : 'Gửi đánh giá của bạn cho trò chơi này' }}
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs text-[#A5ABC0] mb-1">Chấm điểm sao</label>
                                <select name="rating" class="w-full rounded-xl bg-[#181B28] border border-white/10 px-3 py-2.5 text-sm text-[#FFB84D] font-bold">
                                    @for($r = 5; $r >= 1; $r--)
                                        <option value="{{ $r }}" @selected(($userReview->rating ?? 5) == $r)>{{ $r }} Sao {{ str_repeat('★', $r) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs text-[#A5ABC0] mb-1">Nhận xét của bạn</label>
                                <input type="text" name="comment" value="{{ old('comment', $userReview->comment ?? '') }}" placeholder="Chia sẻ cảm nhận về đồ họa, lối chơi..." class="w-full rounded-xl bg-[#181B28] border border-white/10 px-4 py-2.5 text-sm text-white">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#7C5CFF] hover:bg-[#6946ff] text-white text-xs font-extrabold uppercase tracking-wider">
                                {{ $userReview ? 'Cập Nhật Đánh Giá' : 'Gửi Đánh Giá' }}
                            </button>
                        </div>
                    </form>
                @else
                    <div class="bg-[#202437] rounded-2xl p-4 text-center text-sm text-[#A5ABC0]">
                        Vui lòng <a href="{{ route('login') }}" class="text-[#00D4FF] font-bold hover:underline">Đăng nhập</a> để chấm điểm và bình luận về trò chơi này.
                    </div>
                @endauth

                <div class="space-y-4">
                    @forelse($game->approvedReviews as $review)
                        <div class="bg-[#202437]/70 rounded-2xl p-4 border border-white/5 flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <img src="{{ $review->user->avatar_url }}" alt="{{ $review->user->name }}" class="w-10 h-10 rounded-xl object-cover">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-sm text-white">{{ $review->user->name }}</span>
                                        <span class="text-xs font-bold text-[#FFB84D]">{{ str_repeat('★', $review->rating) }}</span>
                                        <span class="text-[11px] text-[#A5ABC0]">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-[#A5ABC0] mt-1">{{ $review->comment ?: 'Trò chơi rất tuyệt vời!' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                @auth
                                    @if(auth()->id() === $review->user_id || auth()->user()->isAdmin())
                                        <form action="{{ route('games.reviews.destroy', $review) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-rose-400 hover:underline">Xóa</button>
                                        </form>
                                    @endif
                                @endauth
                                <button type="button" @click="reportReviewId = {{ $review->id }}; reportModalOpen = true" class="text-xs text-[#A5ABC0] hover:text-rose-400" title="Báo cáo bình luận vi phạm">
                                    🚩
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[#A5ABC0] text-center py-4">Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá trò chơi này!</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Leaderboard & Similar Games Sidebar -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-[#181B28] border border-white/10 rounded-3xl p-6">
                <h2 class="text-lg font-extrabold text-white mb-4 flex items-center gap-2">
                    <span>🏆</span> Bảng Vàng Điểm Cao
                </h2>
                @if($game->scores->isEmpty())
                    <p class="text-xs text-[#A5ABC0]">Chưa có kỷ lục điểm số nào được ghi nhận. Hãy chơi ngay để chiếm vị trí Top 1!</p>
                @else
                    <div class="space-y-2.5">
                        @foreach($game->scores as $idx => $scoreRow)
                            <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-[#202437]">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-black {{ $idx === 0 ? 'bg-[#FFB84D] text-[#10121B]' : 'bg-[#10121B] text-[#A5ABC0]' }}">
                                        {{ $idx + 1 }}
                                    </span>
                                    <span class="text-sm font-bold text-white">{{ $scoreRow->user->name ?? 'Game thủ' }}</span>
                                </div>
                                <span class="text-sm font-black text-[#00D4FF]">{{ number_format($scoreRow->score) }} đ</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-[#181B28] border border-white/10 rounded-3xl p-6">
                <h2 class="text-lg font-extrabold text-white mb-4">🎮 Game Tương Tự</h2>
                <div class="space-y-3">
                    @foreach($relatedGames as $rel)
                        <a href="{{ route('games.show', $rel->slug) }}" class="flex items-center gap-3 p-2 rounded-2xl bg-[#202437]/70 hover:bg-[#202437] transition">
                            <img src="{{ $rel->thumbnail_url }}" alt="{{ $rel->title }}" class="w-20 h-12 rounded-xl object-cover shrink-0">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-white truncate">{{ $rel->title }}</p>
                                <p class="text-xs text-[#A5ABC0]">{{ $rel->category->name ?? 'Arcade' }} • ★ {{ number_format($rel->avg_rating, 1) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div x-show="reportModalOpen" x-transition class="fixed inset-0 z-50 bg-black/75 flex items-center justify-center p-4" style="display: none;">
        <div @click.outside="reportModalOpen = false" class="bg-[#181B28] border border-white/10 rounded-3xl max-w-md w-full p-6 space-y-4">
            <h3 class="text-lg font-extrabold text-white">🚩 Gửi Báo Cáo Lỗi / Vi Phạm</h3>
            <form action="{{ route('games.report.store', $game) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="game_review_id" :value="reportReviewId">
                <div>
                    <label class="block text-xs font-semibold text-[#A5ABC0] mb-1">Loại sự cố</label>
                    <select name="type" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2.5 text-sm text-white">
                        <option value="bug">Lỗi không tải được game / Màn hình đen</option>
                        <option value="broken_asset">Thiếu hình ảnh / Âm thanh</option>
                        <option value="lag">Game giật lag / Hiệu năng kém</option>
                        <option value="review_abuse">Báo cáo bình luận vi phạm</option>
                        <option value="other">Vấn đề khác</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#A5ABC0] mb-1">Mô tả chi tiết</label>
                    <textarea name="description" rows="3" required placeholder="Mô tả chi tiết lỗi bạn gặp phải..." class="w-full rounded-xl bg-[#202437] border border-white/10 p-3.5 text-sm text-white"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="reportModalOpen = false" class="px-4 py-2 rounded-xl bg-[#202437] text-xs font-bold text-[#A5ABC0]">Hủy</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-500 text-xs font-bold text-white">Gửi Báo Cáo</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

