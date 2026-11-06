@extends('layouts.admin')

@php $headerTitle = 'Thêm Trò Chơi Mới & Tải Lên Gói ZIP'; @endphp

@section('content')
<div class="max-w-4xl space-y-6">
    <form action="{{ route('admin.games.store') }}" method="POST" enctype="multipart/form-data" class="bg-[#181B28] border border-white/10 rounded-2xl p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Tên trò chơi *</label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="VD: Pixel Runner Siêu Tốc" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Danh mục *</label>
                <select name="category_id" required class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Tác giả / Studio</label>
                <input type="text" name="author" value="{{ old('author', 'GameHub Studio') }}" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Hướng màn hình</label>
                <select name="orientation" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
                    <option value="landscape">Ngang (Landscape)</option>
                    <option value="portrait">Dọc (Portrait)</option>
                    <option value="any">Tự động (Any)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Trạng thái ban đầu</label>
                <select name="status" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
                    <option value="published">Xuất bản ngay (Published)</option>
                    <option value="draft">Bản nháp (Draft)</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Mô tả ngắn</label>
            <input type="text" name="short_description" value="{{ old('short_description') }}" placeholder="Tóm tắt hấp dẫn trong 1 câu..." class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Mô tả chi tiết</label>
                <textarea name="description" rows="4" class="w-full rounded-xl bg-[#202437] border border-white/10 p-3.5 text-sm text-white">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Hướng dẫn cách chơi</label>
                <textarea name="instructions" rows="4" class="w-full rounded-xl bg-[#202437] border border-white/10 p-3.5 text-sm text-white">{{ old('instructions') }}</textarea>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Điều khiển Bàn phím</label>
                <input type="text" name="controls_keyboard" value="{{ old('controls_keyboard', 'Phím mũi tên / WASD / Space') }}" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Điều khiển Chuột</label>
                <input type="text" name="controls_mouse" value="{{ old('controls_mouse', 'Click chuột trái') }}" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Điều khiển Cảm ứng</label>
                <input type="text" name="controls_touch" value="{{ old('controls_touch', 'Chạm vào màn hình cảm ứng') }}" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
            </div>
        </div>

        <!-- Checkboxes & Tags -->
        <div class="flex flex-wrap gap-6 pt-2">
            <label class="flex items-center gap-2 text-sm text-white">
                <input type="checkbox" name="mobile_support" value="1" checked class="rounded bg-[#202437]"> Hỗ trợ Cảm ứng Mobile
            </label>
            <label class="flex items-center gap-2 text-sm text-white">
                <input type="checkbox" name="is_featured" value="1" class="rounded bg-[#202437]"> Đánh dấu Nổi bật (Featured)
            </label>
            <label class="flex items-center gap-2 text-sm text-white">
                <input type="checkbox" name="is_hot" value="1" class="rounded bg-[#202437]"> Nhãn HOT
            </label>
            <label class="flex items-center gap-2 text-sm text-white">
                <input type="checkbox" name="is_new" value="1" checked class="rounded bg-[#202437]"> Nhãn NEW
            </label>
        </div>

        @if($tags->isNotEmpty())
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-2">Chọn Nhãn (Tags)</label>
                <div class="flex flex-wrap gap-3">
                    @foreach($tags as $tag)
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#202437] text-xs text-white cursor-pointer">
                            <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"> #{{ $tag->name }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Thumbnail & Game ZIP Upload Section -->
        <div class="p-5 rounded-2xl bg-[#202437] border border-[#7C5CFF]/40 space-y-4">
            <h3 class="text-sm font-extrabold text-[#00D4FF] uppercase">📦 Tải Lên Ảnh Đại Diện & Gói Game (.ZIP)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Ảnh Thumbnail (JPG/PNG/SVG/WebP)</label>
                    <input type="file" name="thumbnail" accept="image/*" class="w-full text-xs text-[#A5ABC0]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Tệp nén Game HTML5 (.ZIP, có index.html)</label>
                    <input type="file" name="game_zip" accept=".zip,application/zip" class="w-full text-xs text-[#A5ABC0]">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Mã phiên bản (Version)</label>
                    <input type="text" name="version_number" value="1.0.0" class="w-full rounded-xl bg-[#181B28] border border-white/10 px-3.5 py-2 text-sm text-white">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 text-sm text-emerald-400 font-bold">
                        <input type="checkbox" name="auto_publish" value="1" checked> Tự động Xuất bản nếu vượt qua kiểm tra tương thích
                    </label>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.games.index') }}" class="px-5 py-2.5 rounded-xl bg-[#202437] text-xs font-bold text-[#A5ABC0]">Hủy</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white text-xs font-extrabold uppercase tracking-wider">
                Tạo Game & Giải Nén ZIP
            </button>
        </div>
    </form>
</div>
@endsection

