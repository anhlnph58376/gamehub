@props(['game', 'favorited' => false])

<div
    x-data="{ imgLoaded: false }"
    class="group relative bg-[#202437] rounded-2xl overflow-hidden border border-white/5 hover:border-[#00D4FF]/60 transition-all duration-300 hover:scale-[1.03] hover:shadow-[0_12px_32px_-6px_rgba(124,92,255,0.38)] flex flex-col"
>
    <!-- Thumbnail Container (aspect-video with Skeleton Loading) -->
    <div class="relative aspect-video w-full overflow-hidden bg-[#181B28] block">
        <!-- Skeleton Shimmer Placeholder -->
        <div x-show="!imgLoaded" class="absolute inset-0 bg-gradient-to-r from-[#181B28] via-[#202437] to-[#181B28] animate-pulse"></div>

        <img
            src="{{ $game->thumbnail_url }}"
            alt="{{ $game->title }}"
            loading="lazy"
            @load="imgLoaded = true"
            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 ease-out"
        >
        <div class="absolute inset-0 bg-gradient-to-t from-[#10121B] via-[#10121B]/20 to-transparent opacity-85"></div>

        <!-- Compact Badges Top Left (HOT / NEW / UPDATING / FEATURED) -->
        <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1.5 z-10 pointer-events-none">
            @if($game->is_hot)
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-[#FFB84D] text-[#10121B] shadow-md">
                    🔥 HOT
                </span>
            @endif
            @if($game->is_new)
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-[#00D4FF] text-[#10121B] shadow-md">
                    NEW
                </span>
            @endif
            @if($game->is_featured && !$game->is_hot)
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-[#7C5CFF] text-white shadow-md">
                    UPDATING
                </span>
            @endif
        </div>

        <!-- Smooth Hover Overlay with "Chơi Ngay" CTA & Quick Info -->
        <div class="absolute inset-0 bg-[#10121B]/75 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col items-center justify-center p-3 text-center z-10">
            <a
                href="{{ route('games.play', $game->slug) }}"
                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white text-xs font-black uppercase tracking-wider shadow-lg shadow-[#7C5CFF]/40 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300 hover:scale-105 flex items-center gap-1.5"
            >
                <span>▶</span> Chơi Ngay
            </a>
            <a
                href="{{ route('games.show', $game->slug) }}"
                class="mt-2 text-[11px] font-bold text-[#A5ABC0] hover:text-white underline-offset-2 hover:underline transform translate-y-2 group-hover:translate-y-0 transition-all duration-300"
            >
                Chi tiết & Hướng dẫn
            </a>
        </div>

        <!-- Bottom Info inside Thumbnail -->
        <div class="absolute bottom-2 left-2.5 right-2.5 flex items-center justify-between text-xs pointer-events-none">
            <span class="px-2 py-0.5 rounded-md bg-[#181B28]/90 border border-white/10 text-[#A5ABC0] font-semibold truncate max-w-[65%]">
                {{ $game->category->name ?? 'Arcade' }}
            </span>
            <span class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#181B28]/90 border border-white/10 text-[#FFB84D] font-extrabold">
                ★ {{ number_format($game->avg_rating, 1) }}
            </span>
        </div>
    </div>

    <!-- Favorite Heart Button -->
    @auth
        <form action="{{ route('games.favorite.toggle', $game) }}" method="POST" class="absolute top-2.5 right-2.5 z-20">
            @csrf
            <button type="submit" title="Yêu thích" class="w-8 h-8 rounded-xl bg-[#181B28]/90 hover:bg-[#7C5CFF] border border-white/10 flex items-center justify-center transition {{ $favorited ? 'text-rose-500' : 'text-white/75 hover:text-white' }}">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
            </button>
        </form>
    @endauth

    <!-- Card Details -->
    <div class="p-3.5 flex-1 flex flex-col justify-between">
        <div>
            <a href="{{ route('games.play', $game->slug) }}" class="font-extrabold text-sm text-white group-hover:text-[#00D4FF] transition line-clamp-1">
                {{ $game->title }}
            </a>
            <p class="text-xs text-[#A5ABC0] mt-1 line-clamp-1">
                {{ $game->short_description ?: $game->description }}
            </p>
        </div>

        <div class="mt-3 pt-2.5 border-t border-white/5 flex items-center justify-between text-[11px] text-[#A5ABC0]">
            <span class="flex items-center gap-1 font-medium">
                🔥 {{ number_format($game->play_count) }} lượt chơi
            </span>
            @if($game->mobile_support)
                <span class="text-[#00D4FF] font-bold" title="Tương thích điện thoại & máy tính bảng">📱 PC & Mobile</span>
            @else
                <span class="font-medium">💻 PC</span>
            @endif
        </div>
    </div>
</div>
