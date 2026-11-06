@extends('layouts.app')

@php
    $title = 'Khám Phá Kho Game HTML5';
@endphp

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- Filter Header -->
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-white">🧭 Khám Phá Kho Game HTML5</h1>
                <p class="text-sm text-[#A5ABC0] mt-1">Tìm kiếm và lọc trò chơi theo danh mục, nhãn, thiết bị hỗ trợ và đánh giá</p>
            </div>
            <div class="text-xs font-bold text-[#00D4FF] bg-[#202437] px-4 py-2 rounded-xl border border-white/5">
                Hiển thị {{ $games->total() }} trò chơi
            </div>
        </div>

        <form action="{{ route('games.explore') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-semibold text-[#A5ABC0] mb-1.5">Từ khóa tìm kiếm</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nhập tên game..." class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2.5 text-sm text-white placeholder-[#A5ABC0] focus:outline-none focus:border-[#7C5CFF]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#A5ABC0] mb-1.5">Danh mục</label>
                <select name="category" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-[#7C5CFF]">
                    <option value="">-- Tất cả danh mục --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>
                            {{ $cat->name }} ({{ $cat->published_games_count }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#A5ABC0] mb-1.5">Đánh giá tối thiểu</label>
                <select name="rating" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-[#7C5CFF]">
                    <option value="">-- Mọi mức sao --</option>
                    <option value="4" @selected(request('rating') == '4')>Từ 4.0 sao trở lên ★</option>
                    <option value="3" @selected(request('rating') == '3')>Từ 3.0 sao trở lên ★</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#A5ABC0] mb-1.5">Sắp xếp theo</label>
                <select name="sort" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-[#7C5CFF]">
                    <option value="popular" @selected($sort === 'popular')>🔥 Lượt chơi nhiều nhất</option>
                    <option value="newest" @selected($sort === 'newest')>✨ Mới cập nhật</option>
                    <option value="rating" @selected($sort === 'rating')>⭐ Điểm đánh giá cao</option>
                    <option value="title" @selected($sort === 'title')>🔤 Tên A - Z</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white font-bold text-sm shadow-lg shadow-[#7C5CFF]/25 hover:opacity-95 transition">
                    Áp dụng lọc
                </button>
                <a href="{{ route('games.explore') }}" class="py-2.5 px-3.5 rounded-xl bg-[#202437] text-[#A5ABC0] hover:text-white text-sm font-semibold">
                    Đặt lại
                </a>
            </div>
        </form>

        @if($tags->isNotEmpty())
            <div class="flex items-center flex-wrap gap-2 mt-5 pt-4 border-t border-white/5">
                <span class="text-xs font-semibold text-[#A5ABC0]">Tags phổ biến:</span>
                @foreach($tags as $tag)
                    <a href="{{ route('games.explore', array_merge(request()->query(), ['tag' => $tag->slug])) }}"
                       class="px-3 py-1 rounded-lg text-xs font-semibold transition {{ request('tag') === $tag->slug ? 'bg-[#7C5CFF] text-white' : 'bg-[#202437] text-[#A5ABC0] hover:text-white' }}">
                        #{{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Games Grid -->
    @if($games->isEmpty())
        <div class="bg-[#181B28] border border-white/10 rounded-3xl p-12 text-center space-y-3">
            <p class="text-4xl">🎮</p>
            <h2 class="text-xl font-bold text-white">Không tìm thấy trò chơi phù hợp</h2>
            <p class="text-sm text-[#A5ABC0]">Hãy thử thay đổi bộ lọc hoặc từ khóa tìm kiếm của bạn.</p>
            <a href="{{ route('games.explore') }}" class="inline-block mt-2 px-5 py-2.5 rounded-xl bg-[#7C5CFF] text-white text-sm font-bold">
                Xem toàn bộ kho game
            </a>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($games as $game)
                <x-game-card :game="$game" :favorited="in_array($game->id, $favoriteIds)" />
            @endforeach
        </div>

        <div class="pt-4">
            {{ $games->links() }}
        </div>
    @endif
</div>
@endsection

