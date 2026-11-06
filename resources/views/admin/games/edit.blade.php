@extends('layouts.admin')

@php $headerTitle = 'Quản Lý Game & Upload Center: ' . $game->title; @endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

    <!-- Left Column: Edit Metadata -->
    <div class="lg:col-span-7 space-y-6">
        <form action="{{ route('admin.games.update', $game) }}" method="POST" enctype="multipart/form-data" class="bg-[#181B28] border border-white/10 rounded-2xl p-6 space-y-5">
            @csrf
            @method('PUT')
            <div class="flex items-center justify-between">
                <h2 class="text-base font-extrabold text-white">✏️ Thông Tin Trò Chơi</h2>
                <a href="{{ route('games.play', $game->slug) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-[#00D4FF] text-[#10121B] text-xs font-extrabold">
                    ▶ Mở Trang Chơi Game
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Tên trò chơi</label>
                    <input type="text" name="title" value="{{ old('title', $game->title) }}" required class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Danh mục</label>
                    <select name="category_id" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($game->category_id === $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Tác giả</label>
                    <input type="text" name="author" value="{{ old('author', $game->author) }}" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Hướng màn hình</label>
                    <select name="orientation" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2 text-sm text-white">
                        <option value="landscape" @selected($game->orientation === 'landscape')>Ngang (Landscape)</option>
                        <option value="portrait" @selected($game->orientation === 'portrait')>Dọc (Portrait)</option>
                        <option value="any" @selected($game->orientation === 'any')>Tự động (Any)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Trạng thái</label>
                    <select name="status" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2 text-sm text-white">
                        <option value="published" @selected($game->status === 'published')>Đã xuất bản</option>
                        <option value="draft" @selected($game->status === 'draft')>Bản nháp</option>
                        <option value="hidden" @selected($game->status === 'hidden')>Đang ẩn</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Mô tả ngắn</label>
                <input type="text" name="short_description" value="{{ old('short_description', $game->short_description) }}" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2 text-sm text-white">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Mô tả chi tiết</label>
                <textarea name="description" rows="3" class="w-full rounded-xl bg-[#202437] border border-white/10 p-3.5 text-sm text-white">{{ old('description', $game->description) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Hướng dẫn chơi</label>
                <textarea name="instructions" rows="3" class="w-full rounded-xl bg-[#202437] border border-white/10 p-3.5 text-sm text-white">{{ old('instructions', $game->instructions) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Bàn phím</label>
                    <input type="text" name="controls_keyboard" value="{{ $game->controls['keyboard'] ?? '' }}" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Chuột</label>
                    <input type="text" name="controls_mouse" value="{{ $game->controls['mouse'] ?? '' }}" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Cảm ứng</label>
                    <input type="text" name="controls_touch" value="{{ $game->controls['touch'] ?? '' }}" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3 py-2 text-xs text-white">
                </div>
            </div>

            <div class="flex flex-wrap gap-5">
                <label class="flex items-center gap-2 text-xs text-white">
                    <input type="checkbox" name="mobile_support" value="1" @checked($game->mobile_support)> Hỗ trợ Mobile
                </label>
                <label class="flex items-center gap-2 text-xs text-white">
                    <input type="checkbox" name="is_featured" value="1" @checked($game->is_featured)> Nổi bật (Featured)
                </label>
                <label class="flex items-center gap-2 text-xs text-white">
                    <input type="checkbox" name="is_hot" value="1" @checked($game->is_hot)> HOT
                </label>
                <label class="flex items-center gap-2 text-xs text-white">
                    <input type="checkbox" name="is_new" value="1" @checked($game->is_new)> NEW
                </label>
            </div>

            @php $selectedTagIds = $game->tags->pluck('id')->toArray(); @endphp
            <div class="flex flex-wrap gap-2">
                @foreach($tags as $tag)
                    <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#202437] text-xs text-white">
                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $selectedTagIds))> #{{ $tag->name }}
                    </label>
                @endforeach
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Cập nhật Ảnh Thumbnail</label>
                <input type="file" name="thumbnail" accept="image/*" class="text-xs text-[#A5ABC0]">
            </div>

            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#7C5CFF] text-white text-xs font-extrabold uppercase">
                Lưu Thông Tin Game
            </button>
        </form>
    </div>

    <!-- Right Column: GAME UPLOAD CENTER & VERSION MANAGEMENT -->
    <div class="lg:col-span-5 space-y-6">
        <div class="bg-[#181B28] border border-[#00D4FF]/40 rounded-2xl p-6 space-y-4">
            <h2 class="text-base font-extrabold text-[#00D4FF]">📦 Tải Lên Phiên Bản ZIP Mới</h2>
            <p class="text-xs text-[#A5ABC0]">
                Hệ thống tự động kiểm tra Magic Bytes, chống Zip Slip/ZIP Bomb, chặn tệp thực thi `.php`/`.exe` và phân tích `game.json`.
            </p>

            <form action="{{ route('admin.games.versions.upload', $game) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-white mb-1">Chọn gói .ZIP của game *</label>
                    <input type="file" name="game_zip" required accept=".zip,application/zip" class="w-full text-xs text-[#A5ABC0]">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Số hiệu phiên bản</label>
                        <input type="text" name="version_number" placeholder="Tự động tăng (VD: 1.0.1)" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3 py-2 text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Ghi chú cập nhật</label>
                        <input type="text" name="changelog" placeholder="Sửa lỗi & tối ưu FPS..." class="w-full rounded-xl bg-[#202437] border border-white/10 px-3 py-2 text-xs text-white">
                    </div>
                </div>
                <label class="flex items-center gap-2 text-xs text-emerald-400 font-bold">
                    <input type="checkbox" name="auto_publish" value="1"> Xuất bản ngay lập tức (bỏ qua bước Preview)
                </label>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white text-xs font-extrabold uppercase">
                    Tải Lên & Kiểm Tra Tương Thích
                </button>
            </form>
        </div>

        <!-- Version History, Compatibility Report & 1-Click Rollback -->
        <div class="bg-[#181B28] border border-white/10 rounded-2xl p-6 space-y-4">
            <h2 class="text-base font-extrabold text-white">🕒 Lịch Sử Phiên Bản ({{ $game->versions->count() }})</h2>

            <div class="space-y-4">
                @forelse($game->versions as $ver)
                    <div class="p-4 rounded-2xl bg-[#202437] border {{ $ver->is_active ? 'border-emerald-500/60' : 'border-white/5' }} space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-black text-white">v{{ $ver->version_number }}</span>
                                @if($ver->is_active)
                                    <span class="px-2 py-0.5 rounded bg-emerald-500 text-[#10121B] text-[10px] font-black uppercase">ĐANG CHẠY</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-white/10 text-[#A5ABC0] text-[10px] font-bold uppercase">{{ $ver->status }}</span>
                                @endif
                            </div>
                            <span class="text-[11px] text-[#A5ABC0]">{{ $ver->file_count }} tệp • {{ number_format($ver->extracted_size / 1024, 1) }} KB</span>
                        </div>

                        <p class="text-xs text-[#A5ABC0]">{{ $ver->changelog }}</p>

                        <!-- Compatibility Report Summary -->
                        @if(is_array($ver->compatibility_report))
                            <div class="p-3 rounded-xl bg-[#181B28] text-xs space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-white">Điểm tương thích Sandbox:</span>
                                    <span class="font-black {{ ($ver->compatibility_report['is_compatible'] ?? true) ? 'text-emerald-400' : 'text-rose-400' }}">
                                        {{ $ver->compatibility_report['score'] ?? 100 }}/100
                                    </span>
                                </div>
                                @foreach($ver->compatibility_report['checks'] ?? [] as $check)
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="text-[#A5ABC0]">{{ $check['label'] ?? '' }}</span>
                                        <span class="{{ ($check['passed'] ?? false) ? 'text-emerald-400' : 'text-amber-400' }}">
                                            {{ ($check['passed'] ?? false) ? '✔ Đạt' : '⚠ Chú ý' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Version Actions: Preview, Publish, 1-Click Rollback -->
                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <a href="{{ route('games.play', ['slug' => $game->slug, 'version' => $ver->id]) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-[#181B28] text-[#00D4FF] text-xs font-bold hover:underline">
                                👁️ Preview (Chơi thử)
                            </a>

                            @if(!$ver->is_active)
                                @if($ver->status === 'preview' || $ver->status === 'draft')
                                    <form action="{{ route('admin.games.versions.publish', [$game, $ver]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500 text-[#10121B] text-xs font-extrabold">
                                            🚀 Publish (Xuất bản)
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.games.versions.rollback', [$game, $ver]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#FFB84D] text-[#10121B] text-xs font-extrabold">
                                            ⏪ Rollback về bản này
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-[#A5ABC0]">Chưa có phiên bản nào được tải lên.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

