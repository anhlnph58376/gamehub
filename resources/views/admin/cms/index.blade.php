@extends('layouts.admin')

@php $headerTitle = 'Quản Lý CMS Trang Chủ, Banner & Thông Báo'; @endphp

@section('content')
<div class="space-y-8">
    <!-- Banners Management -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-5 bg-[#181B28] border border-white/10 rounded-2xl p-6 space-y-4">
            <h2 class="text-base font-extrabold text-white">+ Thêm Hero Banner Trang Chủ</h2>
            <form action="{{ route('admin.cms.banners.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Tiêu đề chính *</label>
                    <input type="text" name="title" required placeholder="VD: Đua Top Siêu Phẩm Không Gian" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Mô tả phụ</label>
                    <input type="text" name="subtitle" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Nhãn (Badge)</label>
                        <input type="text" name="badge_text" value="SIÊU PHẨM HOT" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Liên kết tới Game</label>
                        <select name="game_id" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3 py-2 text-xs text-white">
                            <option value="">-- Chọn Game --</option>
                            @foreach($games as $g)
                                <option value="{{ $g->id }}">{{ $g->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-[#7C5CFF] text-white text-xs font-extrabold uppercase">Thêm Banner</button>
            </form>
        </div>

        <div class="lg:col-span-7 bg-[#181B28] border border-white/10 rounded-2xl p-6 space-y-3">
            <h2 class="text-base font-extrabold text-white">Danh Sách Banner Hiện Tại</h2>
            @forelse($banners as $b)
                <div class="p-3.5 rounded-xl bg-[#202437] flex items-center justify-between">
                    <div>
                        <p class="font-bold text-sm text-white">{{ $b->title }}</p>
                        <p class="text-xs text-[#A5ABC0]">{{ $b->subtitle }} • Game: {{ $b->game->title ?? 'Mặc định' }}</p>
                    </div>
                    <form action="{{ route('admin.cms.banners.destroy', $b) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-rose-400 hover:underline">Xóa</button>
                    </form>
                </div>
            @empty
                <p class="text-xs text-[#A5ABC0]">Đang sử dụng Hero Banner tự động từ Game Nổi Bật.</p>
            @endforelse
        </div>
    </div>

    <!-- Homepage Sections Order & Visibility -->
    <div class="bg-[#181B28] border border-white/10 rounded-2xl p-6 space-y-4">
        <h2 class="text-base font-extrabold text-white">📑 Quản Lý Ẩn/Hiện & Thứ Tự Các Khu Vực Trang Chủ</h2>
        <div class="space-y-3">
            @foreach($sections as $sec)
                <form action="{{ route('admin.cms.sections.update', $sec) }}" method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-center p-3 rounded-xl bg-[#202437]">
                    @csrf
                    @method('PUT')
                    <input type="text" name="title" value="{{ $sec->title }}" class="md:col-span-2 rounded-lg bg-[#181B28] border border-white/10 px-3 py-1.5 text-xs text-white font-bold">
                    <input type="text" name="subtitle" value="{{ $sec->subtitle }}" placeholder="Mô tả phụ..." class="md:col-span-2 rounded-lg bg-[#181B28] border border-white/10 px-3 py-1.5 text-xs text-[#A5ABC0]">
                    <div class="flex items-center gap-2">
                        <input type="number" name="position" value="{{ $sec->position }}" class="w-16 rounded-lg bg-[#181B28] border border-white/10 px-2 py-1.5 text-xs text-white">
                        <input type="hidden" name="item_limit" value="{{ $sec->item_limit }}">
                        <label class="flex items-center gap-1 text-xs text-white">
                            <input type="checkbox" name="is_active" value="1" @checked($sec->is_active)> Hiện
                        </label>
                    </div>
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#7C5CFF] text-white text-xs font-bold">Cập nhật</button>
                </form>
            @endforeach
        </div>
    </div>
</div>
@endsection

