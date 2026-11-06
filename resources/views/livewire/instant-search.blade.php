<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <form action="{{ route('games.explore') }}" method="GET" class="relative">
        <input
            type="text"
            name="q"
            wire:model.live.debounce.250ms="query"
            @focus="open = true"
            placeholder="Tìm nhanh game HTML5, thể loại..."
            autocomplete="off"
            class="w-full rounded-xl bg-[#202437] border border-white/10 pl-10 pr-4 py-2 text-sm text-white placeholder-[#A5ABC0] focus:outline-none focus:border-[#7C5CFF] focus:ring-1 focus:ring-[#7C5CFF] transition"
        >
        <svg class="w-4 h-4 text-[#A5ABC0] absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </form>

    @if(mb_strlen(trim($query)) >= 2)
        <div x-show="open" class="absolute left-0 right-0 mt-2 rounded-2xl bg-[#181B28] border border-white/10 shadow-2xl overflow-hidden z-50">
            @if($results->isEmpty())
                <div class="p-4 text-center text-xs text-[#A5ABC0]">
                    Không tìm thấy trò chơi nào khớp với "<span class="text-white font-semibold">{{ $query }}</span>"
                </div>
            @else
                <div class="divide-y divide-white/5">
                    @foreach($results as $item)
                        <a href="{{ route('games.show', $item->slug) }}" class="flex items-center gap-3 p-3 hover:bg-[#202437] transition">
                            <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="w-14 h-9 rounded-lg object-cover shrink-0 bg-[#10121B]">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-white truncate">{{ $item->title }}</p>
                                <p class="text-xs text-[#A5ABC0] truncate">{{ $item->category->name ?? 'Arcade' }} • {{ number_format($item->play_count) }} lượt chơi</p>
                            </div>
                            <span class="text-xs font-bold text-[#FFB84D]">★ {{ number_format($item->avg_rating, 1) }}</span>
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('games.explore', ['q' => $query]) }}" class="block text-center py-2.5 bg-[#202437] text-xs font-bold text-[#00D4FF] hover:underline">
                    Xem tất cả kết quả cho "{{ $query }}" →
                </a>
            @endif
        </div>
    @endif
</div>

