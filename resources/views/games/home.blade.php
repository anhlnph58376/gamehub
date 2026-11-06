@extends('layouts.app')

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-10">

    <!-- System Announcements -->
    @if($announcements->isNotEmpty())
        <div class="space-y-2">
            @foreach($announcements as $ann)
                <div class="rounded-2xl bg-gradient-to-r from-[#7C5CFF]/20 via-[#181B28] to-[#00D4FF]/20 border border-[#7C5CFF]/40 px-4 py-3 flex items-center justify-between gap-4 shadow-lg">
                    <div class="flex items-center gap-3 text-xs sm:text-sm">
                        <span class="px-2.5 py-0.5 rounded-lg bg-[#7C5CFF] text-white text-[11px] font-extrabold uppercase shrink-0">Thông báo</span>
                        <strong class="text-white">{{ $ann->title }}:</strong>
                        <span class="text-[#A5ABC0]">{{ $ann->content }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- 1. HERO BANNER SECTION (Featured Game Spotlight with Animated Background & Pulse Play Button) -->
    @php
        $heroBanner = $banners->first();
        $heroGame = $heroBanner?->game ?? $featuredGames->first();
    @endphp
    <section class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-[#181B28] via-[#202437] to-[#10121B] border border-white/10 shadow-2xl">
        <!-- Animated Glowing Orbs Background -->
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-[#7C5CFF]/25 blur-3xl pointer-events-none animate-float-slow"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-[#00D4FF]/20 blur-3xl pointer-events-none animate-float-slow"></div>

        <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 sm:p-10 lg:p-12 items-center">
            <div class="lg:col-span-7 space-y-5">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#7C5CFF]/20 border border-[#7C5CFF]/50 text-xs font-extrabold text-[#00D4FF] uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-[#00D4FF] animate-ping"></span>
                    <span>🔥 {{ $heroBanner->badge_text ?? 'GAME NỔI BẬT TRONG TUẦN' }}</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white leading-tight tracking-tight">
                    {{ $heroBanner->title ?? ($heroGame->title ?? 'Khám Phá Thế Giới Game HTML5 Đỉnh Cao') }}
                </h1>

                <p class="text-sm sm:text-base lg:text-lg text-[#A5ABC0] max-w-2xl leading-relaxed">
                    {{ $heroBanner->subtitle ?? ($heroGame->short_description ?? 'Chơi ngay lập tức các tựa game Arcade, Hành động, Giải đố mượt mà 60FPS trên trình duyệt máy tính và điện thoại mà không cần tải về.') }}
                </p>

                @if($heroGame)
                    <div class="flex flex-wrap items-center gap-3 text-xs font-bold">
                        <span class="px-3 py-1 rounded-xl bg-[#181B28] border border-white/10 text-[#00D4FF]">
                            🗂️ {{ $heroGame->category->name ?? 'Arcade' }}
                        </span>
                        <span class="px-3 py-1 rounded-xl bg-[#181B28] border border-white/10 text-[#FFB84D]">
                            ★ {{ number_format($heroGame->avg_rating, 1) }} / 5.0
                        </span>
                        <span class="px-3 py-1 rounded-xl bg-[#181B28] border border-white/10 text-[#A5ABC0]">
                            🎮 {{ number_format($heroGame->play_count) }} lượt chơi
                        </span>
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-4 pt-2">
                    @if($heroGame)
                        <a href="{{ route('games.play', $heroGame->slug) }}" class="btn-play-pulse px-8 py-4 rounded-2xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white font-black text-sm sm:text-base hover:scale-105 transition-all duration-300 flex items-center gap-2.5">
                            <span class="text-lg">▶</span>
                            <span>{{ $heroBanner->button_text ?? 'CHƠI NGAY (PLAY NOW)' }}</span>
                        </a>
                        <a href="{{ route('games.show', $heroGame->slug) }}" class="px-6 py-4 rounded-2xl bg-[#181B28]/90 hover:bg-[#202437] border border-white/15 hover:border-[#00D4FF]/60 text-white font-bold text-sm sm:text-base transition">
                            Chi Tiết & Hướng Dẫn
                        </a>
                    @else
                        <a href="{{ route('games.explore') }}" class="btn-play-pulse px-8 py-4 rounded-2xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white font-black text-sm sm:text-base">
                            ▶ Khám Phá Kho Game
                        </a>
                    @endif
                </div>
            </div>

            <!-- Interactive Hero Game Preview Card -->
            <div class="lg:col-span-5">
                @if($heroGame)
                    <a href="{{ route('games.play', $heroGame->slug) }}" class="group block relative rounded-2xl overflow-hidden border-2 border-[#7C5CFF]/60 hover:border-[#00D4FF] shadow-[0_20px_50px_rgba(124,92,255,0.35)] aspect-video bg-[#10121B] transition-all duration-300">
                        <img src="{{ $heroGame->thumbnail_url }}" alt="{{ $heroGame->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#10121B] via-[#10121B]/20 to-transparent"></div>
                        <div class="absolute top-3 left-3 flex gap-2">
                            <span class="px-2.5 py-1 rounded-lg bg-[#FFB84D] text-[#10121B] text-[11px] font-black uppercase shadow">🔥 FEATURED</span>
                            <span class="px-2.5 py-1 rounded-lg bg-[#181B28]/90 text-[#00D4FF] text-[11px] font-bold border border-[#00D4FF]/40">60 FPS HTML5</span>
                        </div>
                        <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-[#00D4FF]">{{ $heroGame->category->name ?? 'Arcade' }}</span>
                                <h3 class="text-lg sm:text-xl font-black text-white truncate">{{ $heroGame->title }}</h3>
                            </div>
                            <span class="w-13 h-13 px-4 py-3 rounded-2xl bg-gradient-to-br from-[#7C5CFF] to-[#00D4FF] text-white flex items-center justify-center font-black text-lg shadow-lg group-hover:scale-110 transition shrink-0">
                                ▶
                            </span>
                        </div>
                    </a>
                @endif
            </div>
        </div>
    </section>

    <!-- 2. QUICK CATEGORIES (Horizontal Scrollable Row like CrazyGames / Poki) -->
    @php
        $categoryIcons = [
            'hanh-dong' => '⚔️',
            'giai-do' => '🧩',
            'arcade' => '🕹️',
            'the-thao' => '⚽',
            'chien-thuat' => '♟️',
            'phieu-luu' => '🗺️',
            'dua-xe' => '🏎️',
            '2-nguoi' => '👥',
        ];
    @endphp
    <section x-data="{ scrollLeft() { $refs.catRow.scrollBy({ left: -280, behavior: 'smooth' }) }, scrollRight() { $refs.catRow.scrollBy({ left: 280, behavior: 'smooth' }) } }">
        <div class="flex items-center justify-between mb-3.5">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-6 rounded-full bg-gradient-to-b from-[#7C5CFF] to-[#00D4FF]"></span>
                <h2 class="text-lg sm:text-xl font-extrabold text-white">Danh Mục Nhanh</h2>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="scrollLeft()" class="hidden sm:flex w-8 h-8 rounded-xl bg-[#181B28] hover:bg-[#202437] border border-white/10 items-center justify-center text-xs text-[#A5ABC0] hover:text-white transition" aria-label="Trượt sang trái">←</button>
                <button type="button" @click="scrollRight()" class="hidden sm:flex w-8 h-8 rounded-xl bg-[#181B28] hover:bg-[#202437] border border-white/10 items-center justify-center text-xs text-[#A5ABC0] hover:text-white transition" aria-label="Trượt sang phải">→</button>
                <a href="{{ route('games.explore') }}" class="text-xs font-extrabold text-[#00D4FF] hover:underline ml-1">Tất cả →</a>
            </div>
        </div>

        <div x-ref="catRow" class="flex items-center gap-3 overflow-x-auto no-scrollbar pb-2">
            <a href="{{ route('games.explore') }}" class="shrink-0 flex items-center gap-2.5 px-4 py-3 rounded-2xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white font-extrabold text-xs sm:text-sm shadow-lg shadow-[#7C5CFF]/25 hover:scale-[1.03] transition min-h-[46px]">
                <span>🌐</span>
                <span>Tất Cả Game</span>
            </a>

            @foreach($categories as $cat)
                @php
                    $icon = $categoryIcons[$cat->slug] ?? '🎮';
                @endphp
                <a href="{{ route('games.category', $cat->slug) }}" class="shrink-0 group flex items-center gap-3 px-4 py-3 rounded-2xl bg-[#181B28] hover:bg-[#202437] border border-white/10 hover:border-[#00D4FF]/60 transition-all duration-200 hover:-translate-y-0.5 min-h-[46px]">
                    <span class="w-8 h-8 rounded-xl bg-[#202437] group-hover:bg-[#7C5CFF] text-base flex items-center justify-center transition">
                        {{ $icon }}
                    </span>
                    <div>
                        <p class="text-xs sm:text-sm font-extrabold text-white group-hover:text-[#00D4FF] transition whitespace-nowrap">{{ $cat->name }}</p>
                        <p class="text-[10px] text-[#A5ABC0]">{{ $cat->published_games_count ?? 0 }} game</p>
                    </div>
                </a>
            @endforeach

            <a href="{{ route('games.explore', ['mobile' => 1]) }}" class="shrink-0 group flex items-center gap-3 px-4 py-3 rounded-2xl bg-[#181B28] hover:bg-[#202437] border border-white/10 hover:border-[#00D4FF]/60 transition-all duration-200 min-h-[46px]">
                <span class="w-8 h-8 rounded-xl bg-[#202437] group-hover:bg-[#00D4FF] text-base flex items-center justify-center transition">📱</span>
                <div>
                    <p class="text-xs sm:text-sm font-extrabold text-white group-hover:text-[#00D4FF] whitespace-nowrap">Chơi Trên Mobile</p>
                    <p class="text-[10px] text-[#A5ABC0]">Cảm ứng mượt mà</p>
                </div>
            </a>
        </div>
    </section>

    <!-- 3. ĐANG THỊNH HÀNH (TRENDING NOW / FEATURED) -->
    @if(!isset($sections['featured']) || $sections['featured']->is_active)
    <section>
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-6 rounded-full bg-[#FFB84D]"></span>
                <div>
                    <h2 class="text-lg sm:text-2xl font-black text-white flex items-center gap-2">
                        <span>🔥</span> Đang Thịnh Hành (Trending Now)
                    </h2>
                    <p class="text-xs text-[#A5ABC0]">{{ $sections['featured']->subtitle ?? 'Những siêu phẩm HTML5 bùng nổ lượt chơi trong ngày' }}</p>
                </div>
            </div>
            <a href="{{ route('games.explore') }}" class="px-3.5 py-1.5 rounded-xl bg-[#181B28] hover:bg-[#202437] border border-white/10 text-xs font-extrabold text-[#00D4FF] transition">
                Xem thêm →
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($featuredGames as $game)
                <x-game-card :game="$game" :favorited="in_array($game->id, $favoriteIds)" />
            @endforeach
        </div>
    </section>
    @endif

    <!-- 4. ĐƯỢC CHƠI NHIỀU NHẤT (MOST PLAYED) -->
    @if(!isset($sections['popular']) || $sections['popular']->is_active)
    <section>
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-6 rounded-full bg-[#7C5CFF]"></span>
                <div>
                    <h2 class="text-lg sm:text-2xl font-black text-white flex items-center gap-2">
                        <span>🏆</span> Được Chơi Nhiều Nhất (Most Played)
                    </h2>
                    <p class="text-xs text-[#A5ABC0]">Cộng đồng game thủ đang đua top sôi động nhất</p>
                </div>
            </div>
            <a href="{{ route('games.explore', ['sort' => 'popular']) }}" class="px-3.5 py-1.5 rounded-xl bg-[#181B28] hover:bg-[#202437] border border-white/10 text-xs font-extrabold text-[#00D4FF] transition">
                Bảng xếp hạng →
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($popularGames as $game)
                <x-game-card :game="$game" :favorited="in_array($game->id, $favoriteIds)" />
            @endforeach
        </div>
    </section>
    @endif

    <!-- 5. MỚI CẬP NHẬT (NEW RELEASES) -->
    @if(!isset($sections['newest']) || $sections['newest']->is_active)
    <section>
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-6 rounded-full bg-[#00D4FF]"></span>
                <div>
                    <h2 class="text-lg sm:text-2xl font-black text-white flex items-center gap-2">
                        <span>✨</span> Mới Cập Nhật (New Releases)
                    </h2>
                    <p class="text-xs text-[#A5ABC0]">Trải nghiệm những tựa game HTML5 vừa phát hành</p>
                </div>
            </div>
            <a href="{{ route('games.explore', ['sort' => 'newest']) }}" class="px-3.5 py-1.5 rounded-xl bg-[#181B28] hover:bg-[#202437] border border-white/10 text-xs font-extrabold text-[#00D4FF] transition">
                Tất cả game mới →
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($newestGames as $game)
                <x-game-card :game="$game" :favorited="in_array($game->id, $favoriteIds)" />
            @endforeach
        </div>
    </section>
    @endif

    <!-- 6. TOP BẢNG VÀNG LƯỢT CHƠI & ĐÁNH GIÁ CAO NHẤT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <section class="lg:col-span-5 bg-[#181B28] border border-white/10 rounded-3xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg sm:text-xl font-extrabold text-white flex items-center gap-2">
                    <span>👑</span> Top 5 Chơi Nhiều Nhất
                </h2>
                <span class="text-[11px] font-bold text-[#FFB84D] uppercase">Thời gian thực</span>
            </div>
            <div class="space-y-3">
                @foreach($mostPlayedGames->take(5) as $idx => $mpGame)
                    <a href="{{ route('games.play', $mpGame->slug) }}" class="group flex items-center gap-3.5 p-2.5 rounded-2xl bg-[#202437]/80 hover:bg-[#202437] border border-white/5 hover:border-[#00D4FF]/50 transition">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-xs shrink-0 {{ $idx === 0 ? 'bg-[#FFB84D] text-[#10121B]' : ($idx === 1 ? 'bg-[#00D4FF] text-[#10121B]' : ($idx === 2 ? 'bg-[#7C5CFF] text-white' : 'bg-[#10121B] text-[#A5ABC0]')) }}">
                            #{{ $idx + 1 }}
                        </span>
                        <img src="{{ $mpGame->thumbnail_url }}" alt="{{ $mpGame->title }}" loading="lazy" class="w-16 h-10 rounded-lg object-cover shrink-0 bg-[#10121B]">
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-extrabold text-white group-hover:text-[#00D4FF] truncate transition">{{ $mpGame->title }}</h3>
                            <p class="text-xs text-[#A5ABC0]">{{ number_format($mpGame->play_count) }} lượt chơi • <span class="text-[#FFB84D]">★ {{ number_format($mpGame->avg_rating, 1) }}</span></p>
                        </div>
                        <span class="px-3 py-1.5 rounded-xl bg-[#7C5CFF] text-white text-xs font-extrabold group-hover:bg-[#00D4FF] group-hover:text-[#10121B] transition">▶ Chơi</span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="lg:col-span-7 bg-[#181B28] border border-white/10 rounded-3xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg sm:text-xl font-extrabold text-white flex items-center gap-2">
                    <span>⭐</span> Đánh Giá Cao Nhất
                </h2>
                <a href="{{ route('games.explore', ['sort' => 'rating']) }}" class="text-xs font-extrabold text-[#00D4FF] hover:underline">Xem tất cả →</a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-2 gap-4">
                @foreach($topRatedGames->take(4) as $game)
                    <x-game-card :game="$game" :favorited="in_array($game->id, $favoriteIds)" />
                @endforeach
            </div>
        </section>
    </div>

    <!-- 7. ĐÃ CHƠI GẦN ĐÂY -->
    @if($recentHistories->isNotEmpty())
        <section class="bg-[#181B28] border border-white/10 rounded-3xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg sm:text-xl font-extrabold text-white flex items-center gap-2">
                    <span>🕒</span> Tiếp Tục Chơi Gần Đây
                </h2>
                <a href="{{ route('user.history') }}" class="text-xs font-extrabold text-[#00D4FF] hover:underline">Lịch sử của bạn →</a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
                @foreach($recentHistories as $history)
                    @if($history->game)
                        <a href="{{ route('games.play', $history->game->slug) }}" class="group bg-[#202437] rounded-2xl p-2.5 border border-white/5 hover:border-[#00D4FF]/60 transition hover:-translate-y-1">
                            <img src="{{ $history->game->thumbnail_url }}" alt="{{ $history->game->title }}" loading="lazy" class="w-full aspect-video rounded-xl object-cover">
                            <p class="text-xs font-extrabold text-white mt-2 truncate group-hover:text-[#00D4FF]">{{ $history->game->title }}</p>
                            <p class="text-[10px] text-[#FFB84D] font-bold">Kỷ lục: {{ number_format($history->score) }} đ</p>
                        </a>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection
