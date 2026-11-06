@extends('layouts.app')

@php $title = 'Hồ Sơ Cá Nhân'; @endphp

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- User Banner -->
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-3xl object-cover border-2 border-[#7C5CFF]">
            <div>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                    <h1 class="text-2xl font-black text-white">{{ $user->name }}</h1>
                    @foreach($user->roles as $role)
                        <span class="px-2.5 py-0.5 rounded-lg bg-[#7C5CFF]/20 text-[#00D4FF] text-xs font-bold uppercase">{{ $role->name }}</span>
                    @endforeach
                </div>
                <p class="text-sm text-[#A5ABC0] mt-1">{{ $user->email }}</p>
                <p class="text-xs text-[#A5ABC0] mt-2 max-w-xl">{{ $user->bio ?: 'Chưa cập nhật giới thiệu bản thân.' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3 shrink-0">
            <div class="bg-[#202437] rounded-2xl p-4 text-center border border-white/5">
                <p class="text-xl font-black text-[#FFB84D]">{{ number_format($user->total_points) }}</p>
                <p class="text-[11px] text-[#A5ABC0]">Điểm thành tích</p>
            </div>
            <div class="bg-[#202437] rounded-2xl p-4 text-center border border-white/5">
                <p class="text-xl font-black text-[#00D4FF]">{{ $stats['games_played'] }}</p>
                <p class="text-[11px] text-[#A5ABC0]">Game đã chơi</p>
            </div>
            <div class="bg-[#202437] rounded-2xl p-4 text-center border border-white/5">
                <p class="text-xl font-black text-rose-400">{{ $stats['favorites_count'] }}</p>
                <p class="text-[11px] text-[#A5ABC0]">Yêu thích</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Update Profile Form -->
        <div class="lg:col-span-5 bg-[#181B28] border border-white/10 rounded-3xl p-6 space-y-5">
            <h2 class="text-lg font-extrabold text-white">⚙️ Cập Nhật Thông Tin Cá Nhân</h2>
            <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Tên hiển thị</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Địa chỉ Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Giới thiệu ngắn (Bio)</label>
                    <textarea name="bio" rows="3" class="w-full rounded-xl bg-[#202437] border border-white/10 p-3.5 text-sm text-white">{{ old('bio', $user->bio) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Ảnh đại diện mới (Tùy chọn)</label>
                    <input type="file" name="avatar" accept="image/*" class="w-full text-xs text-[#A5ABC0]">
                </div>
                <div class="pt-2 border-t border-white/10">
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Đổi mật khẩu mới (Để trống nếu giữ nguyên)</label>
                    <input type="password" name="password" placeholder="Mật khẩu mới..." class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white mb-2">
                    <input type="password" name="password_confirmation" placeholder="Xác nhận mật khẩu mới..." class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
                </div>
                <button type="submit" class="w-full py-3 rounded-xl bg-[#7C5CFF] hover:bg-[#6946ff] text-white font-extrabold text-sm transition">
                    Lưu Thay Đổi
                </button>
            </form>
        </div>

        <!-- Achievements, Favorites & Recent History -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-[#181B28] border border-white/10 rounded-3xl p-6">
                <h2 class="text-lg font-extrabold text-white mb-4">🏅 Huy Hiệu Thành Tích ({{ $user->achievements->count() }})</h2>
                @if($user->achievements->isEmpty())
                    <p class="text-xs text-[#A5ABC0]">Hãy tích cực chơi game và ghi điểm cao để mở khóa các huy hiệu danh giá!</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($user->achievements as $ach)
                            <div class="p-3.5 rounded-2xl bg-[#202437] border border-[#FFB84D]/30 flex items-center gap-3">
                                <span class="text-2xl">🏆</span>
                                <div>
                                    <p class="text-sm font-bold text-white">{{ $ach->title }}</p>
                                    <p class="text-xs text-[#A5ABC0]">{{ $ach->description }} (+{{ $ach->points }} điểm)</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-[#181B28] border border-white/10 rounded-3xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-extrabold text-white">🕒 Lịch Sử Chơi Gần Đây</h2>
                    <a href="{{ route('user.history') }}" class="text-xs font-bold text-[#00D4FF] hover:underline">Xem tất cả →</a>
                </div>
                @if($user->histories->isEmpty())
                    <p class="text-xs text-[#A5ABC0]">Bạn chưa chơi trò chơi nào.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($user->histories->take(6) as $hist)
                            @if($hist->game)
                                <a href="{{ route('games.play', $hist->game->slug) }}" class="flex items-center gap-3 p-2.5 rounded-2xl bg-[#202437] hover:bg-[#282d45] transition">
                                    <img src="{{ $hist->game->thumbnail_url }}" alt="{{ $hist->game->title }}" class="w-16 h-10 rounded-lg object-cover">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-white truncate">{{ $hist->game->title }}</p>
                                        <p class="text-[11px] text-[#A5ABC0]">Điểm: {{ number_format($hist->score) }} • {{ $hist->played_at->diffForHumans() }}</p>
                                    </div>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

