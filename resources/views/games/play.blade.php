@extends('layouts.app')

@php
    $title = 'Chơi ' . $game->title;
    $metaDescription = "Chơi ngay {$game->title} trực tuyến miễn phí trên trình duyệt tại GameHub.";
    $playSrc = $activeVersion?->play_url;
@endphp

@section('content')
<div
    class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-5 space-y-8"
    x-data="{
        reportModalOpen: false,
        reportReviewId: null,
        muted: false,
        gamePaused: false,
        latestScore: 0,
        favorited: @js((bool) $isFavorited),
        favoriteCount: @js((int) $game->favorite_count),
        async toggleFav() {
            try {
                const res = await fetch('{{ route('games.favorite.toggle', $game) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.favorited = data.favorited;
                    this.favoriteCount = data.favorite_count;
                    if (window.GameHubAudio) window.GameHubAudio.notify('success');
                }
            } catch (e) {}
        }
    }"
>

    <!-- Breadcrumb & Admin Preview Notice -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <nav class="flex items-center gap-2 text-xs text-[#A5ABC0]">
            <a href="{{ route('home') }}" class="hover:text-white transition">Trang chủ</a>
            <span>/</span>
            @if($game->category)
                <a href="{{ route('games.category', $game->category->slug) }}" class="hover:text-white transition">{{ $game->category->name }}</a>
                <span>/</span>
            @endif
            <span class="text-white font-bold">{{ $game->title }}</span>
        </nav>

        @if(request('version') && auth()->check() && auth()->user()->isAdmin())
            <div class="px-4 py-1.5 rounded-xl bg-[#FFB84D]/20 border border-[#FFB84D]/50 text-[#FFB84D] text-xs font-extrabold flex items-center gap-3">
                <span>⚡ CHẾ ĐỘ ADMIN PREVIEW (Phiên bản v{{ $activeVersion?->version_number }})</span>
                <a href="{{ route('admin.games.edit', $game) }}" class="underline text-white">Quay lại Upload Center</a>
            </div>
        @endif
    </div>

    <!-- Mobile Landscape Warning -->
    @if($game->orientation === 'landscape')
        <div class="sm:hidden portrait:flex hidden items-center gap-3 rounded-2xl bg-[#FFB84D]/15 border border-[#FFB84D]/40 px-4 py-3 text-xs text-[#FFB84D]">
            <span class="text-lg">🔄</span>
            <span>Trò chơi này hiển thị tối ưu ở chế độ <strong>Xoay ngang màn hình (Landscape)</strong> hoặc nhấn nút Toàn màn hình.</span>
        </div>
    @endif

    <!-- MAIN LAYOUT: LEFT (GAME CONTAINER + INFO + REVIEWS) & RIGHT (SIDEBAR RELATED GAMES) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

        <!-- Left Primary Column (9 cols on XL) -->
        <div class="xl:col-span-9 space-y-6">

            <!-- 1. CORE GAME CONTAINER & TOOLBAR (CrazyGames / Poki Cinema Box) -->
            <div class="bg-[#181B28] border border-white/10 rounded-2xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.65)]">
                <!-- Iframe Viewport -->
                <div id="game-player-wrapper" class="relative w-full aspect-video max-h-[76vh] bg-[#0B0D14] flex items-center justify-center mx-auto">
                    @if($playSrc)
                        <iframe
                            id="game-sandbox-iframe"
                            src="{{ $playSrc }}"
                            title="{{ $game->title }}"
                            sandbox="allow-scripts allow-same-origin"
                            allow="fullscreen; gamepad; autoplay"
                            loading="eager"
                            class="w-full h-full border-0"
                        ></iframe>
                    @else
                        <div class="text-center p-8 space-y-3">
                            <p class="text-3xl">⚠️</p>
                            <p class="text-base font-bold text-white">Trò chơi chưa có gói phiên bản hoạt động</p>
                            <p class="text-xs text-[#A5ABC0]">Quản trị viên vui lòng tải lên tệp ZIP và xuất bản phiên bản cho trò chơi này.</p>
                        </div>
                    @endif
                </div>

                <!-- Bottom Game Control Bar -->
                <div class="bg-[#202437] px-4 sm:px-6 py-3 border-t border-white/10 flex flex-wrap items-center justify-between gap-3">
                    <!-- Game Title & Quick Metadata inside Bar -->
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="{{ $game->thumbnail_url }}" alt="{{ $game->title }}" class="w-11 h-11 rounded-xl object-cover border border-white/10 hidden sm:block shrink-0">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h1 class="text-base sm:text-lg font-black text-white truncate">{{ $game->title }}</h1>
                                @if($activeVersion)
                                    <span class="px-2 py-0.5 rounded-md bg-[#181B28] text-[#00D4FF] text-[10px] font-extrabold border border-[#00D4FF]/30">v{{ $activeVersion->version_number }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-xs text-[#A5ABC0]">
                                <span>{{ $game->author ?: 'GameHub Studio' }}</span>
                                <span>•</span>
                                <span>🎮 {{ number_format($game->play_count) }} lượt chơi</span>
                                <span x-show="latestScore > 0" class="text-[#FFB84D] font-extrabold" style="display: none;">
                                    • 🏆 Điểm: <span x-text="latestScore"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Action Buttons -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Favorite (Heart) Button -->
                        @auth
                            <button
                                type="button"
                                @click="toggleFav()"
                                class="min-h-[42px] px-3.5 py-2 rounded-xl border text-xs font-extrabold transition flex items-center gap-1.5"
                                :class="favorited ? 'bg-rose-500/20 border-rose-500/50 text-rose-400' : 'bg-[#181B28] hover:bg-[#10121B] border-white/10 text-white'"
                                title="Yêu thích trò chơi"
                            >
                                <span x-text="favorited ? '❤️' : '🤍'"></span>
                                <span class="hidden sm:inline" x-text="favorited ? 'Đã thích' : 'Yêu thích'"></span>
                                <span class="text-[11px] opacity-80" x-text="'(' + favoriteCount + ')'"></span>
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="min-h-[42px] px-3.5 py-2 rounded-xl bg-[#181B28] hover:bg-[#10121B] border border-white/10 text-xs font-extrabold text-white flex items-center gap-1.5">
                                <span>🤍</span>
                                <span class="hidden sm:inline">Yêu thích</span>
                                <span class="text-[11px] text-[#A5ABC0]">({{ $game->favorite_count }})</span>
                            </a>
                        @endauth

                        <!-- Pause / Resume Game & Audio -->
                        <button
                            type="button"
                            @click="gamePaused = !gamePaused; toggleGamePauseState(gamePaused)"
                            class="min-h-[42px] px-3.5 py-2 rounded-xl bg-[#181B28] hover:bg-[#10121B] border border-white/10 text-xs font-extrabold text-white transition flex items-center gap-1.5"
                            title="Tạm dừng / Tiếp tục trò chơi và âm thanh"
                        >
                            <span x-text="gamePaused ? '▶ Tiếp tục' : '⏸ Tạm dừng'"></span>
                        </button>

                        <!-- Mute / Unmute Audio -->
                        <button
                            type="button"
                            @click="muted = !muted; toggleGameMute(muted)"
                            class="min-h-[42px] px-3.5 py-2 rounded-xl bg-[#181B28] hover:bg-[#10121B] border border-white/10 text-xs font-extrabold text-white transition flex items-center gap-1.5"
                            title="Tắt / Bật âm thanh"
                        >
                            <span x-text="muted ? '🔇 Tắt âm' : '🔊 Âm thanh'"></span>
                        </button>

                        <!-- Restart / Reload Game -->
                        <button
                            type="button"
                            onclick="reloadGameIframe()"
                            class="min-h-[42px] px-3.5 py-2 rounded-xl bg-[#181B28] hover:bg-[#10121B] border border-white/10 text-xs font-extrabold text-white transition flex items-center gap-1.5"
                            title="Tải lại trò chơi"
                        >
                            <span>🔄</span>
                            <span class="hidden sm:inline">Tải lại</span>
                        </button>

                        <!-- Report Bug -->
                        <button
                            type="button"
                            @click="reportReviewId = null; reportModalOpen = true; if (window.GameHubAudio) window.GameHubAudio.modalOpen();"
                            class="min-h-[42px] px-3.5 py-2 rounded-xl bg-[#181B28] hover:bg-rose-500/20 border border-white/10 text-xs font-extrabold text-rose-400 transition flex items-center gap-1.5"
                            title="Báo lỗi trò chơi"
                        >
                            <span>🚩</span>
                            <span class="hidden sm:inline">Báo lỗi</span>
                        </button>

                        <!-- Fullscreen Button -->
                        <button
                            type="button"
                            onclick="toggleGameFullscreen()"
                            class="min-h-[42px] px-4 py-2 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white text-xs font-black uppercase tracking-wider shadow-lg shadow-[#7C5CFF]/30 hover:scale-105 transition flex items-center gap-1.5"
                        >
                            <span>⛶</span>
                            <span>Toàn màn hình</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. GAME DETAILS & VISUAL KEYCAP CONTROLS GUIDE -->
            <div class="bg-[#181B28] border border-white/10 rounded-2xl p-6 sm:p-7 space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-white/10">
                    <div class="flex flex-wrap items-center gap-2">
                        @if($game->category)
                            <a href="{{ route('games.category', $game->category->slug) }}" class="px-3 py-1 rounded-xl bg-[#7C5CFF]/20 border border-[#7C5CFF]/40 text-xs font-extrabold text-[#00D4FF]">
                                🗂️ {{ $game->category->name }}
                            </a>
                        @endif
                        @foreach($game->tags as $tag)
                            <a href="{{ route('games.explore', ['tag' => $tag->slug]) }}" class="px-2.5 py-1 rounded-lg bg-[#202437] text-xs font-semibold text-[#A5ABC0] hover:text-white transition">
                                #{{ $tag->name }}
                            </a>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-3 text-xs">
                        <span class="px-3 py-1 rounded-xl bg-[#202437] text-[#FFB84D] font-extrabold">
                            ★ {{ number_format($game->avg_rating, 1) }} ({{ $game->review_count }} đánh giá)
                        </span>
                        <a href="{{ route('games.show', $game->slug) }}" class="text-[#00D4FF] font-bold hover:underline">
                            Trang thông tin đầy đủ →
                        </a>
                    </div>
                </div>

                <!-- Visual Controls Guide with Keycap Icons ([W][A][S][D], [Spacebar], Mouse, Touch) -->
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold text-white mb-3 flex items-center gap-2">
                        <span>🎮</span> Hướng Dẫn Điều Khiển Trực Quan
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Keyboard Keycaps -->
                        <div class="bg-[#202437] rounded-2xl p-4 border border-white/5 flex flex-col justify-between gap-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-extrabold text-[#00D4FF] uppercase tracking-wider">⌨️ Bàn phím</span>
                                <div class="flex items-center gap-1">
                                    <kbd class="kbd-key">W</kbd>
                                    <kbd class="kbd-key">A</kbd>
                                    <kbd class="kbd-key">S</kbd>
                                    <kbd class="kbd-key">D</kbd>
                                    <kbd class="kbd-key">Space</kbd>
                                </div>
                            </div>
                            <p class="text-xs text-[#A5ABC0] leading-relaxed">
                                {{ $game->controls['keyboard'] ?? 'Sử dụng các phím [W][A][S][D] hoặc Phím mũi tên để di chuyển, [Spacebar] để nhảy / hành động.' }}
                            </p>
                        </div>

                        <!-- Mouse Control -->
                        <div class="bg-[#202437] rounded-2xl p-4 border border-white/5 flex flex-col justify-between gap-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-extrabold text-[#FFB84D] uppercase tracking-wider">🖱️ Chuột</span>
                                <span class="kbd-key">🖱️ Chuột trái</span>
                            </div>
                            <p class="text-xs text-[#A5ABC0] leading-relaxed">
                                {{ $game->controls['mouse'] ?? 'Nhấp Chuột trái hoặc kéo thả trực tiếp trong khung hình trò chơi.' }}
                            </p>
                        </div>

                        <!-- Mobile Touch -->
                        <div class="bg-[#202437] rounded-2xl p-4 border border-white/5 flex flex-col justify-between gap-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-extrabold text-emerald-400 uppercase tracking-wider">📱 Cảm ứng Mobile</span>
                                <span class="kbd-key">👆 Chạm / Vuốt</span>
                            </div>
                            <p class="text-xs text-[#A5ABC0] leading-relaxed">
                                {{ $game->controls['touch'] ?? 'Chạm hoặc vuốt trực tiếp lên màn hình điện thoại / máy tính bảng.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Game Description & Instructions -->
                <div class="space-y-2 pt-2 border-t border-white/5">
                    <h2 class="text-base font-extrabold text-white">📖 Giới Thiệu Trò Chơi</h2>
                    <p class="text-sm text-[#A5ABC0] leading-relaxed">{{ $game->description }}</p>
                    @if($game->instructions)
                        <p class="text-xs text-[#00D4FF] font-semibold pt-1">💡 Mẹo chơi: {{ $game->instructions }}</p>
                    @endif
                </div>
            </div>

            <!-- 3. INTERACTIVE 1-5 STAR REVIEWS & REAL-TIME COMMENTS SECTION -->
            <div
                class="bg-[#181B28] border border-white/10 rounded-2xl p-6 sm:p-7 space-y-6"
                x-data="{
                    selectedRating: @js((int) ($userReview->rating ?? 5)),
                    hoverRating: 0,
                    commentText: @js($userReview->comment ?? ''),
                    submitting: false,
                    feedbackMsg: '',
                    avgRating: @js(number_format($game->avg_rating, 1)),
                    reviewCount: @js((int) $game->review_count),
                    async submitReviewAjax(e) {
                        this.submitting = true;
                        this.feedbackMsg = '';
                        try {
                            const res = await fetch('{{ route('games.reviews.store', $game) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    rating: this.selectedRating,
                                    comment: this.commentText
                                })
                            });
                            if (res.ok) {
                                const data = await res.json();
                                this.avgRating = data.avg_rating;
                                this.reviewCount = data.review_count;
                                this.feedbackMsg = data.message;
                            } else {
                                e.target.submit();
                            }
                        } catch (err) {
                            e.target.submit();
                        } finally {
                            this.submitting = false;
                        }
                    }
                }"
            >
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg sm:text-xl font-extrabold text-white flex items-center gap-2">
                            <span>⭐</span> Đánh Giá & Bình Luận Cộng Đồng
                        </h2>
                        <p class="text-xs text-[#A5ABC0]">Chia sẻ cảm nhận và mẹo đạt điểm cao của bạn</p>
                    </div>
                    <div class="flex items-center gap-2 bg-[#202437] px-4 py-2 rounded-2xl border border-white/10">
                        <span class="text-xl font-black text-[#FFB84D]">★ <span x-text="avgRating">{{ number_format($game->avg_rating, 1) }}</span></span>
                        <span class="text-xs text-[#A5ABC0]">(<span x-text="reviewCount">{{ $game->review_count }}</span> đánh giá)</span>
                    </div>
                </div>

                @auth
                    <form action="{{ route('games.reviews.store', $game) }}" method="POST" @submit.prevent="submitReviewAjax($event)" class="bg-[#202437] rounded-2xl p-5 border border-white/10 space-y-4">
                        @csrf
                        <input type="hidden" name="rating" :value="selectedRating">

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <span class="text-xs sm:text-sm font-extrabold text-white">Chọn số sao đánh giá của bạn:</span>
                            <!-- Interactive 1-5 Star Selector -->
                            <div class="flex items-center gap-1" @mouseleave="hoverRating = 0">
                                <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                    <button
                                        type="button"
                                        @mouseenter="hoverRating = star"
                                        @click="selectedRating = star"
                                        class="text-2xl focus:outline-none transition transform hover:scale-125"
                                        :class="(hoverRating || selectedRating) >= star ? 'text-[#FFB84D]' : 'text-white/20'"
                                    >
                                        ★
                                    </button>
                                </template>
                                <span class="ml-2 text-xs font-extrabold text-[#FFB84D]" x-text="selectedRating + ' / 5 sao'"></span>
                            </div>
                        </div>

                        <div>
                            <textarea
                                name="comment"
                                x-model="commentText"
                                rows="2"
                                placeholder="Viết bình luận của bạn về đồ họa, âm thanh hoặc lối chơi..."
                                class="w-full rounded-xl bg-[#181B28] border border-white/10 px-4 py-3 text-sm text-white placeholder-[#A5ABC0] focus:outline-none focus:border-[#00D4FF]"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span x-show="feedbackMsg" x-text="'✅ ' + feedbackMsg" class="text-xs font-bold text-emerald-400" style="display: none;"></span>
                            <button
                                type="submit"
                                :disabled="submitting"
                                class="ml-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white text-xs font-black uppercase tracking-wider shadow-lg shadow-[#7C5CFF]/25 hover:opacity-95 transition"
                            >
                                <span x-text="submitting ? 'Đang lưu...' : 'Gửi Đánh Giá'">Gửi Đánh Giá</span>
                            </button>
                        </div>
                    </form>
                @else
                    <div class="bg-[#202437] rounded-2xl p-4 text-center text-sm text-[#A5ABC0]">
                        Vui lòng <a href="{{ route('login') }}" class="text-[#00D4FF] font-extrabold hover:underline">Đăng nhập</a> để chấm điểm sao và bình luận trực tiếp.
                    </div>
                @endauth

                <!-- Reviews List -->
                <div class="space-y-3">
                    @forelse($game->approvedReviews as $review)
                        <div class="bg-[#202437]/75 rounded-2xl p-4 border border-white/5 flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <img src="{{ $review->user->avatar_url }}" alt="{{ $review->user->name }}" class="w-10 h-10 rounded-xl object-cover shrink-0">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-extrabold text-sm text-white">{{ $review->user->name }}</span>
                                        <span class="text-xs font-bold text-[#FFB84D]">{{ str_repeat('★', $review->rating) }}</span>
                                        <span class="text-[11px] text-[#A5ABC0]">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-[#A5ABC0] mt-1">{{ $review->comment ?: 'Trò chơi rất cuốn hút!' }}</p>
                                </div>
                            </div>
                            <button type="button" @click="reportReviewId = {{ $review->id }}; reportModalOpen = true" class="text-xs text-[#A5ABC0] hover:text-rose-400" title="Báo cáo bình luận">
                                🚩
                            </button>
                        </div>
                    @empty
                        <p class="text-xs text-[#A5ABC0] text-center py-4">Chưa có bình luận nào. Hãy là người đầu tiên đánh giá tựa game này!</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Sidebar Column (3 cols on XL): Related Games & High Scores -->
        <aside class="xl:col-span-3 space-y-6">

            <!-- Related Games ("Game tương tự") -->
            <div class="bg-[#181B28] border border-white/10 rounded-2xl p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-white flex items-center gap-2">
                        <span>🔥</span> Game Tương Tự
                    </h2>
                    <a href="{{ route('games.explore') }}" class="text-xs font-bold text-[#00D4FF] hover:underline">Tất cả</a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-1 gap-3">
                    @foreach($relatedGames as $relGame)
                        <a href="{{ route('games.play', $relGame->slug) }}" class="group flex flex-col xl:flex-row items-start xl:items-center gap-3 p-2 rounded-xl bg-[#202437]/80 hover:bg-[#202437] border border-white/5 hover:border-[#00D4FF]/60 transition">
                            <img src="{{ $relGame->thumbnail_url }}" alt="{{ $relGame->title }}" loading="lazy" class="w-full xl:w-24 aspect-video rounded-lg object-cover shrink-0 bg-[#10121B]">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-xs sm:text-sm font-extrabold text-white group-hover:text-[#00D4FF] truncate transition">{{ $relGame->title }}</h3>
                                <p class="text-[11px] text-[#A5ABC0] truncate">{{ $relGame->category->name ?? 'Arcade' }}</p>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px]">
                                    <span class="text-[#FFB84D] font-extrabold">★ {{ number_format($relGame->avg_rating, 1) }}</span>
                                    <span class="text-[#A5ABC0]">• {{ number_format($relGame->play_count) }} lượt</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Leaderboard Box -->
            <div class="bg-[#181B28] border border-white/10 rounded-2xl p-5 space-y-3.5">
                <h2 class="text-base font-extrabold text-white flex items-center gap-2">
                    <span>🏆</span> Bảng Xếp Hạng Kỷ Lục
                </h2>
                @if($game->scores->isEmpty())
                    <p class="text-xs text-[#A5ABC0]">Chơi ngay để ghi danh vị trí Top 1 bảng vàng!</p>
                @else
                    <div class="space-y-2">
                        @foreach($game->scores as $idx => $scoreRow)
                            <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-[#202437]">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-black shrink-0 {{ $idx === 0 ? 'bg-[#FFB84D] text-[#10121B]' : ($idx === 1 ? 'bg-[#00D4FF] text-[#10121B]' : 'bg-[#10121B] text-[#A5ABC0]') }}">
                                        {{ $idx + 1 }}
                                    </span>
                                    <span class="text-xs font-bold text-white truncate">{{ $scoreRow->user->name ?? 'Game thủ' }}</span>
                                </div>
                                <span class="text-xs font-black text-[#00D4FF] shrink-0">{{ number_format($scoreRow->score) }} đ</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </aside>

    </div>

    <!-- Quick Report Modal -->
    <div x-show="reportModalOpen" x-transition class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.outside="reportModalOpen = false" class="bg-[#181B28] border border-white/10 rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <h3 class="text-lg font-extrabold text-white">🚩 Báo Lỗi Trò Chơi "{{ $game->title }}"</h3>
            <form action="{{ route('games.report.store', $game) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="game_review_id" :value="reportReviewId">
                <div>
                    <label class="block text-xs font-semibold text-[#A5ABC0] mb-1">Loại lỗi gặp phải</label>
                    <select name="type" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2.5 text-sm text-white">
                        <option value="bug">Game không khởi động / Lỗi màn hình</option>
                        <option value="broken_asset">Thiếu hình ảnh / Âm thanh</option>
                        <option value="lag">Giật lag / Không điều khiển được</option>
                        <option value="review_abuse">Báo cáo bình luận vi phạm</option>
                        <option value="other">Lỗi khác</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#A5ABC0] mb-1">Chi tiết lỗi</label>
                    <textarea name="description" rows="3" required placeholder="Mô tả lỗi để kỹ thuật viên kiểm tra..." class="w-full rounded-xl bg-[#202437] border border-white/10 p-3.5 text-sm text-white"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="reportModalOpen = false" class="px-4 py-2 rounded-xl bg-[#202437] text-xs font-bold text-[#A5ABC0]">Đóng</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-500 text-xs font-bold text-white">Gửi Báo Lỗi</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    const sessionToken = @json($sessionToken);
    const sessionRecordUrl = @json(route('games.session.record', $game));
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const allowedOrigin = window.location.origin;
    const playStartTime = Date.now();
    let bestScoreInSession = 0;

    try {
        const recentKey = 'gamehub_recent_games';
        const currentItem = {
            slug: @json($game->slug),
            title: @json($game->title),
            played_at: new Date().toISOString()
        };
        const existing = JSON.parse(localStorage.getItem(recentKey) || '[]').filter(g => g.slug !== currentItem.slug);
        existing.unshift(currentItem);
        localStorage.setItem(recentKey, JSON.stringify(existing.slice(0, 12)));
    } catch (e) {}

    function reloadGameIframe() {
        const iframe = document.getElementById('game-sandbox-iframe');
        if (iframe) {
            iframe.src = iframe.src;
        }
    }

    function toggleGameFullscreen() {
        const wrapper = document.getElementById('game-player-wrapper');
        if (!wrapper) return;
        if (!document.fullscreenElement) {
            wrapper.requestFullscreen().catch(() => {});
        } else {
            document.exitFullscreen().catch(() => {});
        }
    }

    function toggleGameMute(mutedState) {
        const iframe = document.getElementById('game-sandbox-iframe');
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.postMessage({
                type: 'GAMEHUB_MUTE_TOGGLE',
                muted: Boolean(mutedState)
            }, '*');
        }
    }

    function toggleGamePauseState(pausedState) {
        const iframe = document.getElementById('game-sandbox-iframe');
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.postMessage({
                type: pausedState ? 'GAMEHUB_PAUSE' : 'GAMEHUB_RESUME'
            }, '*');
        }
    }

    function syncSessionData(scoreValue = 0) {
        const elapsedSeconds = Math.max(1, Math.round((Date.now() - playStartTime) / 1000));
        if (scoreValue > bestScoreInSession) {
            bestScoreInSession = scoreValue;
        }

        fetch(sessionRecordUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                session_token: sessionToken,
                duration_seconds: elapsedSeconds,
                score: bestScoreInSession
            }),
            keepalive: true
        }).catch(() => {});
    }

    window.addEventListener('message', function (event) {
        if (event.origin !== allowedOrigin && event.origin !== 'null') {
            return;
        }
        if (!event.data || typeof event.data !== 'object') {
            return;
        }

        if (event.data.type === 'GAMEHUB_SCORE' && typeof event.data.score === 'number') {
            const safeScore = Math.max(0, Math.min(999999999, Math.floor(event.data.score)));
            syncSessionData(safeScore);
        }
    });

    setInterval(() => syncSessionData(bestScoreInSession), 30000);
    window.addEventListener('beforeunload', () => syncSessionData(bestScoreInSession));
</script>
@endpush
@endsection
