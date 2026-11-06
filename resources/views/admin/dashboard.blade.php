@extends('layouts.admin')

@php $headerTitle = 'Bảng Điều Khiển & Thống Kê Thời Gian Thực'; @endphp

@section('content')
<div class="space-y-8">
    <!-- Top Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-[#181B28] border border-white/10 rounded-2xl p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-[#A5ABC0] uppercase">Tổng Số Game</span>
                <span class="text-xl">🎮</span>
            </div>
            <p class="text-3xl font-black text-white mt-2">{{ number_format($metrics['total_games']) }}</p>
            <p class="text-xs text-emerald-400 mt-1">{{ $metrics['published_games'] }} đang xuất bản</p>
        </div>

        <div class="bg-[#181B28] border border-white/10 rounded-2xl p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-[#A5ABC0] uppercase">Tổng Lượt Chơi</span>
                <span class="text-xl">🔥</span>
            </div>
            <p class="text-3xl font-black text-[#00D4FF] mt-2">{{ number_format($metrics['total_plays']) }}</p>
            <p class="text-xs text-[#A5ABC0] mt-1">Trên {{ $metrics['total_categories'] }} danh mục</p>
        </div>

        <div class="bg-[#181B28] border border-white/10 rounded-2xl p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-[#A5ABC0] uppercase">Thành Viên</span>
                <span class="text-xl">👥</span>
            </div>
            <p class="text-3xl font-black text-[#7C5CFF] mt-2">{{ number_format($metrics['total_users']) }}</p>
            <p class="text-xs text-[#A5ABC0] mt-1">{{ number_format($metrics['total_reviews']) }} đánh giá đã gửi</p>
        </div>

        <div class="bg-[#181B28] border border-white/10 rounded-2xl p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-[#A5ABC0] uppercase">Báo Cáo Chờ Xử Lý</span>
                <span class="text-xl">🚩</span>
            </div>
            <p class="text-3xl font-black text-[#FFB84D] mt-2">{{ number_format($metrics['pending_reports']) }}</p>
            <a href="{{ route('admin.reports.index') }}" class="text-xs text-[#00D4FF] hover:underline mt-1 inline-block">Kiểm tra ngay →</a>
        </div>
    </div>

    <!-- Daily Play Chart & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7 bg-[#181B28] border border-white/10 rounded-2xl p-6">
            <h2 class="text-base font-extrabold text-white mb-4">📈 Biểu Đồ Phiên Chơi 7 Ngày Gần Nhất</h2>
            @php
                $maxCount = max(collect($metrics['daily_plays'])->max('count'), 1);
            @endphp
            <div class="grid grid-cols-7 gap-3 items-end h-48 pt-6">
                @foreach($metrics['daily_plays'] as $day)
                    @php $heightPct = max(12, round(($day['count'] / $maxCount) * 100)); @endphp
                    <div class="flex flex-col items-center gap-2 h-full justify-end">
                        <span class="text-xs font-bold text-[#00D4FF]">{{ $day['count'] }}</span>
                        <div class="w-full max-w-[42px] rounded-t-xl bg-gradient-to-t from-[#7C5CFF] to-[#00D4FF]" style="height: {{ $heightPct }}%"></div>
                        <span class="text-[11px] text-[#A5ABC0]">{{ $day['date'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lg:col-span-5 bg-[#181B28] border border-white/10 rounded-2xl p-6 space-y-4">
            <h2 class="text-base font-extrabold text-white">⚡ Thao Tác Quản Trị Nhanh</h2>
            <div class="grid grid-cols-1 gap-3">
                <a href="{{ route('admin.games.create') }}" class="p-4 rounded-xl bg-[#202437] hover:bg-[#7C5CFF]/20 border border-white/10 flex items-center justify-between transition">
                    <div>
                        <p class="text-sm font-bold text-white">Tải Lên Gói Game ZIP Mới</p>
                        <p class="text-xs text-[#A5ABC0]">Tự động kiểm tra bảo mật & giải nén phiên bản</p>
                    </div>
                    <span class="text-lg">📦</span>
                </a>
                <a href="{{ route('admin.cms.index') }}" class="p-4 rounded-xl bg-[#202437] hover:bg-[#7C5CFF]/20 border border-white/10 flex items-center justify-between transition">
                    <div>
                        <p class="text-sm font-bold text-white">Quản Lý Giao Diện Trang Chủ</p>
                        <p class="text-xs text-[#A5ABC0]">Tùy chỉnh Banner, thứ tự các khu vực hiển thị</p>
                    </div>
                    <span class="text-lg">🎨</span>
                </a>
                <a href="{{ route('admin.audit-logs.index') }}" class="p-4 rounded-xl bg-[#202437] hover:bg-[#7C5CFF]/20 border border-white/10 flex items-center justify-between transition">
                    <div>
                        <p class="text-sm font-bold text-white">Nhật Ký Bảo Mật (Audit Logs)</p>
                        <p class="text-xs text-[#A5ABC0]">Theo dõi toàn bộ hoạt động quản trị viên</p>
                    </div>
                    <span class="text-lg">🛡️</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Top Games & Newest Users -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7 bg-[#181B28] border border-white/10 rounded-2xl p-6">
            <h2 class="text-base font-extrabold text-white mb-4">🏆 Top Game Có Lượt Chơi Cao Nhất</h2>
            <div class="space-y-3">
                @foreach($metrics['top_games'] as $g)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-[#202437]">
                        <div class="flex items-center gap-3">
                            <img src="{{ $g->thumbnail_url }}" alt="{{ $g->title }}" class="w-14 h-9 rounded-lg object-cover">
                            <div>
                                <p class="text-sm font-bold text-white">{{ $g->title }}</p>
                                <p class="text-xs text-[#A5ABC0]">{{ $g->category->name ?? 'Arcade' }} • ★ {{ number_format($g->avg_rating, 1) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-black text-[#00D4FF]">{{ number_format($g->play_count) }} lượt</p>
                            <a href="{{ route('admin.games.edit', $g) }}" class="text-xs text-[#A5ABC0] hover:text-white">Quản lý →</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lg:col-span-5 bg-[#181B28] border border-white/10 rounded-2xl p-6">
            <h2 class="text-base font-extrabold text-white mb-4">🆕 Thành Viên Mới Đăng Ký</h2>
            <div class="space-y-3">
                @foreach($metrics['newest_users'] as $u)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-[#202437]">
                        <div class="flex items-center gap-3">
                            <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}" class="w-9 h-9 rounded-xl object-cover">
                            <div>
                                <p class="text-sm font-bold text-white">{{ $u->name }}</p>
                                <p class="text-xs text-[#A5ABC0]">{{ $u->email }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-[#A5ABC0]">{{ $u->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

