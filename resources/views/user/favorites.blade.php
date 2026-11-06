@extends('layouts.app')

@php $title = 'Game Yêu Thích Của Tôi'; @endphp

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-white">❤️ Danh Sách Game Yêu Thích</h1>
            <p class="text-sm text-[#A5ABC0]">Các trò chơi bạn đã lưu lại để truy cập nhanh</p>
        </div>
        <a href="{{ route('user.profile') }}" class="px-4 py-2 rounded-xl bg-[#181B28] text-xs font-bold text-[#00D4FF]">← Hồ sơ cá nhân</a>
    </div>

    @if($favorites->isEmpty())
        <div class="bg-[#181B28] border border-white/10 rounded-3xl p-12 text-center space-y-3">
            <p class="text-base text-[#A5ABC0]">Bạn chưa thêm trò chơi nào vào danh sách Yêu thích.</p>
            <a href="{{ route('games.explore') }}" class="inline-block px-5 py-2.5 rounded-xl bg-[#7C5CFF] text-white text-xs font-bold">Khám phá game ngay</a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
            @foreach($favorites as $game)
                <x-game-card :game="$game" :favorited="true" />
            @endforeach
        </div>
        <div>{{ $favorites->links() }}</div>
    @endif
</div>
@endsection

