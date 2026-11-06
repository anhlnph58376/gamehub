@extends('layouts.app')

@php
    $title = 'Danh mục ' . $category->name;
    $metaDescription = $category->description ?: "Chơi các tựa game {$category->name} HTML5 trực tuyến miễn phí hay nhất tại GameHub.";
@endphp

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-[#7C5CFF]/20 text-[#00D4FF] text-xs font-bold uppercase mb-2">
                Danh mục trò chơi
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white">{{ $category->name }}</h1>
            <p class="text-sm text-[#A5ABC0] mt-1 max-w-2xl">{{ $category->description }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('games.category', ['slug' => $category->slug, 'sort' => 'popular']) }}" class="px-4 py-2 rounded-xl text-xs font-bold {{ $sort === 'popular' ? 'bg-[#7C5CFF] text-white' : 'bg-[#202437] text-[#A5ABC0]' }}">
                Phổ biến nhất
            </a>
            <a href="{{ route('games.category', ['slug' => $category->slug, 'sort' => 'newest']) }}" class="px-4 py-2 rounded-xl text-xs font-bold {{ $sort === 'newest' ? 'bg-[#7C5CFF] text-white' : 'bg-[#202437] text-[#A5ABC0]' }}">
                Mới nhất
            </a>
            <a href="{{ route('games.category', ['slug' => $category->slug, 'sort' => 'rating']) }}" class="px-4 py-2 rounded-xl text-xs font-bold {{ $sort === 'rating' ? 'bg-[#7C5CFF] text-white' : 'bg-[#202437] text-[#A5ABC0]' }}">
                Đánh giá cao
            </a>
        </div>
    </div>

    @if($games->isEmpty())
        <div class="bg-[#181B28] border border-white/10 rounded-3xl p-12 text-center">
            <p class="text-base text-[#A5ABC0]">Hiện chưa có trò chơi nào trong danh mục này.</p>
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

