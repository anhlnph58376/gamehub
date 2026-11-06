@extends('layouts.admin')

@php $headerTitle = 'Quản Lý Kho Game & Upload Center'; @endphp

@section('content')
<div class="space-y-6">
    <!-- Filter & Search -->
    <div class="bg-[#181B28] border border-white/10 rounded-2xl p-5 flex flex-wrap items-center justify-between gap-4">
        <form action="{{ route('admin.games.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm tên game..." class="rounded-xl bg-[#202437] border border-white/10 px-4 py-2 text-sm text-white">
            <select name="category_id" class="rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
                <option value="">-- Tất cả danh mục --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="published" @selected(request('status') === 'published')>Đã xuất bản</option>
                <option value="draft" @selected(request('status') === 'draft')>Bản nháp</option>
                <option value="hidden" @selected(request('status') === 'hidden')>Đang ẩn</option>
                <option value="trashed" @selected(request('status') === 'trashed')>Thùng rác (Đã xóa mềm)</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#7C5CFF] text-white text-xs font-bold">Lọc kết quả</button>
        </form>

        <a href="{{ route('admin.games.create') }}" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white text-xs font-extrabold uppercase tracking-wider">
            + Thêm Game & Upload ZIP
        </a>
    </div>

    <!-- Games Table -->
    <div class="bg-[#181B28] border border-white/10 rounded-2xl overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-[#202437] text-xs uppercase text-[#A5ABC0]">
                <tr>
                    <th class="px-5 py-3.5">Trò chơi</th>
                    <th class="px-5 py-3.5">Danh mục</th>
                    <th class="px-5 py-3.5">Phiên bản</th>
                    <th class="px-5 py-3.5">Trạng thái</th>
                    <th class="px-5 py-3.5">Lượt chơi / Sao</th>
                    <th class="px-5 py-3.5 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($games as $game)
                    <tr class="hover:bg-[#202437]/50">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $game->thumbnail_url }}" alt="{{ $game->title }}" class="w-14 h-9 rounded-lg object-cover bg-[#10121B]">
                                <div>
                                    <p class="font-bold text-white">{{ $game->title }}</p>
                                    <p class="text-xs text-[#A5ABC0]">/{{ $game->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-[#A5ABC0]">{{ $game->category->name ?? 'N/A' }}</td>
                        <td class="px-5 py-4">
                            @if($game->currentVersion)
                                <span class="px-2.5 py-1 rounded-lg bg-[#202437] text-[#00D4FF] text-xs font-bold">
                                    v{{ $game->currentVersion->version_number }}
                                </span>
                            @else
                                <span class="text-xs text-rose-400">Chưa có ZIP</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($game->trashed())
                                <span class="px-2.5 py-1 rounded-lg bg-rose-500/20 text-rose-400 text-xs font-bold">Thùng rác</span>
                            @elseif($game->status === 'published')
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-400 text-xs font-bold">Đã xuất bản</span>
                            @else
                                <span class="px-2.5 py-1 rounded-lg bg-amber-500/20 text-amber-300 text-xs font-bold">{{ strtoupper($game->status) }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-xs text-[#A5ABC0]">
                            <div>🎮 {{ number_format($game->play_count) }} lượt</div>
                            <div class="text-[#FFB84D] font-bold">★ {{ number_format($game->avg_rating, 1) }} ({{ $game->review_count }})</div>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($game->trashed())
                                    <form action="{{ route('admin.games.restore', $game->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500 text-white text-xs font-bold">Khôi phục</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.games.toggle-featured', $game) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg text-xs font-bold {{ $game->is_featured ? 'bg-[#FFB84D] text-[#10121B]' : 'bg-[#202437] text-[#A5ABC0]' }}">
                                            {{ $game->is_featured ? '★ Featured' : '☆ Thường' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('games.play', $game->slug) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-[#202437] text-[#00D4FF] text-xs font-bold">Chơi thử</a>
                                    <a href="{{ route('admin.games.edit', $game) }}" class="px-3 py-1.5 rounded-lg bg-[#7C5CFF] text-white text-xs font-bold">Sửa & ZIP</a>
                                    <form action="{{ route('admin.games.destroy', $game) }}" method="POST" onsubmit="return confirm('Chuyển trò chơi này vào thùng rác?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-500/20 text-rose-400 text-xs font-bold">Xóa</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-[#A5ABC0]">Chưa có trò chơi nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $games->links() }}</div>
</div>
@endsection

