@extends('layouts.app')

@php $title = 'Lịch Sử Chơi Game'; @endphp

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-white">🕒 Lịch Sử Chơi Game Của Bạn</h1>
            <p class="text-sm text-[#A5ABC0]">Theo dõi thời gian giải trí và kỷ lục điểm số của bạn</p>
        </div>
        <a href="{{ route('user.profile') }}" class="px-4 py-2 rounded-xl bg-[#181B28] text-xs font-bold text-[#00D4FF]">← Hồ sơ cá nhân</a>
    </div>

    @if($histories->isEmpty())
        <div class="bg-[#181B28] border border-white/10 rounded-3xl p-12 text-center">
            <p class="text-sm text-[#A5ABC0]">Chưa có lịch sử chơi game nào được ghi nhận.</p>
        </div>
    @else
        <div class="bg-[#181B28] border border-white/10 rounded-3xl overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#202437] text-xs uppercase text-[#A5ABC0]">
                    <tr>
                        <th class="px-6 py-4">Trò chơi</th>
                        <th class="px-6 py-4">Danh mục</th>
                        <th class="px-6 py-4">Điểm đạt được</th>
                        <th class="px-6 py-4">Thời lượng</th>
                        <th class="px-6 py-4">Lần chơi cuối</th>
                        <th class="px-6 py-4 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($histories as $item)
                        @if($item->game)
                            <tr class="hover:bg-[#202437]/50">
                                <td class="px-6 py-4 font-bold text-white">{{ $item->game->title }}</td>
                                <td class="px-6 py-4 text-[#A5ABC0]">{{ $item->game->category->name ?? 'Arcade' }}</td>
                                <td class="px-6 py-4 font-bold text-[#FFB84D]">{{ number_format($item->score) }}</td>
                                <td class="px-6 py-4 text-[#A5ABC0]">{{ max(1, (int) ceil($item->duration_seconds / 60)) }} phút</td>
                                <td class="px-6 py-4 text-[#A5ABC0]">{{ $item->played_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('games.play', $item->game->slug) }}" class="px-3.5 py-1.5 rounded-xl bg-[#7C5CFF] text-white text-xs font-bold">Chơi tiếp</a>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $histories->links() }}</div>
    @endif
</div>
@endsection

