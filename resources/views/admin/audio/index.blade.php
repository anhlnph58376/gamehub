@extends('layouts.admin')

@php
    $title = 'Quản lý Âm thanh (Audio System)';
    $headerTitle = '🔊 Trung Tâm Quản Lý Âm Thanh Website & Game (Audio Asset Manager)';
@endphp

@section('content')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

<div class="space-y-6" x-data="{
    previewUrl: null,
    previewName: '',
    previewSize: '',
    assignModalOpen: false,
    assignAssetId: null,
    assignAssetTitle: '',
    editModalOpen: false,
    editAsset: {},
    handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) {
            this.previewUrl = null;
            return;
        }
        if (this.previewUrl) {
            URL.revokeObjectURL(this.previewUrl);
        }
        this.previewUrl = URL.createObjectURL(file);
        this.previewName = file.name;
        this.previewSize = (file.size / 1024).toFixed(1) + ' KB';
    }
}">

    <!-- 1. Thống kê tổng quan Kho Âm Thanh -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-[#181B28] border border-white/10 rounded-2xl p-4">
            <p class="text-xs text-[#A5ABC0]">Tổng Audio Assets</p>
            <p class="text-2xl font-black text-white mt-1">{{ number_format($stats['total_count']) }}</p>
        </div>
        <div class="bg-[#181B28] border border-white/10 rounded-2xl p-4">
            <p class="text-xs text-[#A5ABC0]">Đang Hoạt Động</p>
            <p class="text-2xl font-black text-emerald-400 mt-1">{{ number_format($stats['active_count']) }}</p>
        </div>
        <div class="bg-[#181B28] border border-white/10 rounded-2xl p-4">
            <p class="text-xs text-[#A5ABC0]">Nhạc nền (Music)</p>
            <p class="text-2xl font-black text-[#7C5CFF] mt-1">{{ number_format($stats['music_count']) }}</p>
        </div>
        <div class="bg-[#181B28] border border-white/10 rounded-2xl p-4">
            <p class="text-xs text-[#A5ABC0]">Hiệu ứng (SFX)</p>
            <p class="text-2xl font-black text-[#00D4FF] mt-1">{{ number_format($stats['sfx_count']) }}</p>
        </div>
        <div class="bg-[#181B28] border border-white/10 rounded-2xl p-4">
            <p class="text-xs text-[#A5ABC0]">Môi trường (Ambient)</p>
            <p class="text-2xl font-black text-[#FFB84D] mt-1">{{ number_format($stats['ambient_count']) }}</p>
        </div>
        <div class="bg-[#181B28] border border-white/10 rounded-2xl p-4">
            <p class="text-xs text-[#A5ABC0]">Tổng Dung Lượng</p>
            <p class="text-xl font-black text-white mt-1">{{ number_format($stats['total_size_bytes'] / 1024, 1) }} KB</p>
        </div>
    </div>

    <!-- 2. Form Upload Audio Mới (Có Nghe Thử Trước Khi Lưu & Khai Báo Bản Quyền) -->
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-6 space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 pb-4">
            <div>
                <h2 class="text-lg font-extrabold text-white">⬆️ Tải Lên Âm Thanh Mới (MP3 / OGG / WAV)</h2>
                <p class="text-xs text-[#A5ABC0]">Hỗ trợ nghe thử trước khi lưu, kiểm tra chữ ký Magic Bytes và chặn tuyệt đối tệp thực thi máy chủ.</p>
            </div>
            <form action="{{ route('admin.audio.synthesize-defaults') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#202437] hover:bg-[#7C5CFF] border border-white/10 text-xs font-extrabold text-[#00D4FF] hover:text-white transition">
                    🔄 Khôi phục / Tổng hợp lại Bộ Âm Thanh Chuẩn
                </button>
            </form>
        </div>

        <form action="{{ route('admin.audio.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Tên âm thanh *</label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="VD: Nhạc nền Trang chủ Synthwave" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Phân loại (Category) *</label>
                <select name="category" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
                    <option value="sfx">🔊 Sound Effect (SFX - Hiệu ứng)</option>
                    <option value="music">🎵 Music (Nhạc nền)</option>
                    <option value="ambient">🌌 Ambient (Âm thanh môi trường)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Phạm vi sử dụng (Scope) *</label>
                <select name="scope" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
                    <option value="both">🌐 Website & Game</option>
                    <option value="website">🖥️ Giao diện Website (Howler.js)</option>
                    <option value="game">🎮 Trong Game (Phaser 3)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Mã sự kiện Website (Event Key)</label>
                <input type="text" name="event_key" value="{{ old('event_key') }}" list="website-event-keys" placeholder="VD: ui.click, bgm.home..." class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
                <datalist id="website-event-keys">
                    <option value="ui.click">Click nút bấm</option>
                    <option value="ui.hover">Di chuột (Hover)</option>
                    <option value="ui.success">Thông báo Thành công</option>
                    <option value="ui.error">Thông báo Lỗi</option>
                    <option value="ui.modal_open">Mở Modal</option>
                    <option value="ui.modal_close">Đóng Modal</option>
                    <option value="bgm.home">Nhạc nền Trang chủ</option>
                    <option value="bgm.explore">Nhạc nền Khám phá</option>
                </datalist>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Âm lượng mặc định (0.0 - 1.0) *</label>
                <input type="number" step="0.05" min="0" max="1" name="default_volume" value="{{ old('default_volume', '0.80') }}" required class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
            </div>

            <div class="flex items-center gap-6 pt-5">
                <label class="inline-flex items-center gap-2 text-xs font-bold text-white cursor-pointer">
                    <input type="checkbox" name="loop" value="1" class="rounded bg-[#202437] border-white/20 text-[#7C5CFF]">
                    <span>🔁 Lặp lại liên tục (Loop)</span>
                </label>
                <label class="inline-flex items-center gap-2 text-xs font-bold text-emerald-400 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded bg-[#202437] border-white/20 text-emerald-500">
                    <span>✅ Kích hoạt ngay</span>
                </label>
            </div>

            <!-- Copyright & Licensing Fields -->
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Nguồn gốc âm thanh *</label>
                <select name="source_type" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
                    <option value="project_generated">🛠️ Dự án tự sản xuất (Project Synthesized)</option>
                    <option value="ai_generated">🤖 Do AI tạo ra (Có quyền sử dụng)</option>
                    <option value="public_domain">🌍 Phạm vi công cộng (Public Domain / CC0)</option>
                    <option value="licensed">📜 Có giấy phép rõ ràng (Licensed)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Tên giấy phép bản quyền *</label>
                <input type="text" name="license_name" value="{{ old('license_name', 'CC0 1.0 Public Domain') }}" required class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Tác giả / Nguồn ghi công</label>
                <input type="text" name="author_attribution" value="{{ old('author_attribution', 'GameHub Audio Studio') }}" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
            </div>

            <!-- Optional Direct Game Assignment -->
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Gắn trực tiếp vào Game (Tùy chọn)</label>
                <select name="game_id" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
                    <option value="">-- Không gắn hoặc gắn sau --</option>
                    @foreach($games as $g)
                        <option value="{{ $g->id }}">{{ $g->title }} ({{ $g->slug }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Mã khóa trong Game (Sound Key)</label>
                <input type="text" name="sound_key" value="{{ old('sound_key') }}" placeholder="VD: bgm_main, sfx_jump, sfx_laser..." class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Điều kiện sử dụng / Mô tả</label>
                <input type="text" name="description" value="{{ old('description') }}" placeholder="Mô tả chi tiết và điều kiện bản quyền..." class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
            </div>

            <!-- File Input + Pre-save Audio Preview -->
            <div class="md:col-span-2 bg-[#202437] rounded-2xl p-4 border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <label class="block text-xs font-extrabold text-[#00D4FF] mb-1">Chọn tệp âm thanh (.mp3, .ogg, .wav - Tối đa 15MB) *</label>
                    <input type="file" name="audio_file" accept=".mp3,.ogg,.wav,audio/mpeg,audio/ogg,audio/wav" required @change="handleFileSelect($event)" class="text-xs text-[#A5ABC0] file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#7C5CFF] file:text-white">
                </div>

                <!-- Live Pre-Save Preview Player -->
                <div x-show="previewUrl" class="flex items-center gap-3 bg-[#181B28] px-3.5 py-2 rounded-xl border border-[#00D4FF]/40" style="display: none;">
                    <div class="text-xs">
                        <p class="font-bold text-white truncate max-w-[160px]" x-text="'🎧 Nghe thử: ' + previewName"></p>
                        <p class="text-[10px] text-[#00D4FF]" x-text="'Dung lượng: ' + previewSize"></p>
                    </div>
                    <audio :src="previewUrl" controls class="h-8 w-44"></audio>
                </div>
            </div>

            <div class="flex items-end justify-end">
                <button type="submit" class="w-full py-3 px-6 rounded-2xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-[#7C5CFF]/30 hover:opacity-95 transition">
                    💾 Lưu Audio Asset
                </button>
            </div>
        </form>
    </div>

    <!-- 3. Bộ lọc & Danh sách Audio Assets -->
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-6 space-y-5">
        <form action="{{ route('admin.audio.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên hoặc event_key..." class="rounded-xl bg-[#202437] border border-white/10 px-4 py-2 text-xs text-white placeholder-[#A5ABC0]">
            <select name="category" class="rounded-xl bg-[#202437] border border-white/10 px-3 py-2 text-xs text-white">
                <option value="">-- Tất cả phân loại --</option>
                <option value="music" @selected(request('category') === 'music')>🎵 Music (Nhạc nền)</option>
                <option value="sfx" @selected(request('category') === 'sfx')>🔊 SFX (Hiệu ứng)</option>
                <option value="ambient" @selected(request('category') === 'ambient')>🌌 Ambient (Môi trường)</option>
            </select>
            <select name="scope" class="rounded-xl bg-[#202437] border border-white/10 px-3 py-2 text-xs text-white">
                <option value="">-- Tất cả phạm vi --</option>
                <option value="website" @selected(request('scope') === 'website')>🖥️ Website</option>
                <option value="game" @selected(request('scope') === 'game')>🎮 Game</option>
                <option value="both" @selected(request('scope') === 'both')>🌐 Cả hai</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#7C5CFF] text-white text-xs font-bold">Lọc</button>
            <a href="{{ route('admin.audio.index') }}" class="px-3 py-2 rounded-xl bg-[#202437] text-[#A5ABC0] text-xs">Đặt lại</a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-white/10 text-[#A5ABC0] uppercase">
                        <th class="py-3 px-3">Âm thanh & Bản quyền</th>
                        <th class="py-3 px-3">Phân loại / Event Key</th>
                        <th class="py-3 px-3">Nghe thử trực tiếp</th>
                        <th class="py-3 px-3">Gắn với Game</th>
                        <th class="py-3 px-3">Dung lượng</th>
                        <th class="py-3 px-3">Trạng thái</th>
                        <th class="py-3 px-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($audioAssets as $asset)
                        <tr class="hover:bg-[#202437]/50 transition">
                            <td class="py-3.5 px-3">
                                <p class="font-extrabold text-sm text-white">{{ $asset->title }}</p>
                                <p class="text-[11px] text-[#A5ABC0] line-clamp-1">{{ $asset->description }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="px-2 py-0.5 rounded bg-[#202437] text-[10px] text-[#00D4FF] font-semibold">
                                        📜 {{ $asset->license_name }}
                                    </span>
                                    <span class="text-[10px] text-[#A5ABC0]">({{ $asset->source_type }})</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-3 space-y-1">
                                <span class="inline-block px-2 py-0.5 rounded-md font-bold uppercase text-[10px] {{ $asset->category === 'music' ? 'bg-[#7C5CFF]/25 text-[#7C5CFF]' : ($asset->category === 'ambient' ? 'bg-[#FFB84D]/20 text-[#FFB84D]' : 'bg-[#00D4FF]/20 text-[#00D4FF]') }}">
                                    {{ $asset->category }} ({{ $asset->scope }})
                                </span>
                                @if($asset->event_key)
                                    <p class="font-mono text-[11px] text-white bg-[#10121B] px-2 py-0.5 rounded inline-block">{{ $asset->event_key }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-3">
                                <audio src="{{ $asset->url }}" controls preload="none" class="h-8 w-44"></audio>
                            </td>
                            <td class="py-3.5 px-3">
                                <div class="space-y-1">
                                    @foreach($asset->gameAssignments as $assign)
                                        <div class="flex items-center gap-1.5 bg-[#10121B] px-2 py-1 rounded-lg">
                                            <span class="font-bold text-white">{{ $assign->game->title ?? 'Game' }}</span>
                                            <span class="text-[#00D4FF] font-mono">[{{ $assign->sound_key }}]</span>
                                            <form action="{{ route('admin.audio.assignments.destroy', $assign) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-400 hover:underline ml-1" title="Gỡ khỏi game">✕</button>
                                            </form>
                                        </div>
                                    @endforeach
                                    <button
                                        type="button"
                                        @click="assignAssetId = {{ $asset->id }}; assignAssetTitle = @js($asset->title); assignModalOpen = true"
                                        class="px-2.5 py-1 rounded-lg bg-[#202437] hover:bg-[#7C5CFF] text-[#00D4FF] hover:text-white font-bold text-[11px] transition"
                                    >
                                        + Gắn vào Game
                                    </button>
                                </div>
                            </td>
                            <td class="py-3.5 px-3">
                                <p class="font-bold text-white">{{ $asset->formatted_size }}</p>
                                <p class="text-[10px] text-[#A5ABC0] uppercase">{{ $asset->extension }} • Vol {{ number_format($asset->default_volume * 100) }}%</p>
                            </td>
                            <td class="py-3.5 px-3">
                                <form action="{{ route('admin.audio.toggle-active', $asset) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg font-bold text-[11px] {{ $asset->is_active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40' }}">
                                        {{ $asset->is_active ? 'Đang bật' : 'Đang tắt' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-3 text-right space-x-1.5">
                                <button
                                    type="button"
                                    @click="editAsset = @js($asset); editModalOpen = true"
                                    class="px-2.5 py-1.5 rounded-lg bg-[#202437] hover:bg-[#00D4FF] hover:text-[#10121B] text-white font-bold transition"
                                >
                                    Sửa / Thay file
                                </button>
                                <form action="{{ route('admin.audio.destroy', $asset) }}" method="POST" class="inline" onsubmit="return confirm('Xóa vĩnh viễn tệp âm thanh này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-500/20 hover:bg-rose-500 text-rose-300 hover:text-white font-bold transition">
                                        Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-[#A5ABC0]">Chưa có tệp âm thanh nào. Nhấn "Khôi phục / Tổng hợp lại Bộ Âm Thanh Chuẩn" ở góc trên để tạo ngay!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $audioAssets->links() }}
        </div>
    </div>

    <!-- Modal Gắn Audio vào Game -->
    <div x-show="assignModalOpen" x-transition class="fixed inset-0 z-50 bg-black/75 flex items-center justify-center p-4" style="display: none;">
        <div @click.outside="assignModalOpen = false" class="bg-[#181B28] border border-white/10 rounded-3xl max-w-md w-full p-6 space-y-4">
            <h3 class="text-base font-extrabold text-white">🎮 Gắn Âm Thanh Vào Game</h3>
            <p class="text-xs text-[#00D4FF] font-bold" x-text="assignAssetTitle"></p>

            <form :action="'{{ url('/admin/audio') }}/' + assignAssetId + '/assign-game'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Chọn Trò Chơi *</label>
                    <select name="game_id" required class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2.5 text-sm text-white">
                        @foreach($games as $g)
                            <option value="{{ $g->id }}">{{ $g->title }} ({{ $g->slug }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Mã khóa âm thanh trong Phaser 3 (Sound Key) *</label>
                    <input type="text" name="sound_key" required placeholder="VD: bgm_main, sfx_jump, sfx_explosion..." class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2.5 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Mức âm lượng (0.0 - 1.0) *</label>
                    <input type="number" step="0.05" min="0" max="1" name="volume" value="0.80" required class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2.5 text-sm text-white">
                </div>
                <div class="flex items-center gap-4">
                    <label class="inline-flex items-center gap-2 text-xs text-white">
                        <input type="checkbox" name="loop" value="1">
                        <span>🔁 Phát lặp (Loop)</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs text-white">
                        <input type="checkbox" name="preload" value="1" checked>
                        <span>⚡ Preload khi vào game</span>
                    </label>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="assignModalOpen = false" class="px-4 py-2 rounded-xl bg-[#202437] text-xs font-bold text-[#A5ABC0]">Đóng</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#7C5CFF] text-xs font-extrabold text-white">Lưu Liên Kết</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Sửa / Thay Thế File Audio -->
    <div x-show="editModalOpen" x-transition class="fixed inset-0 z-50 bg-black/75 flex items-center justify-center p-4" style="display: none;">
        <div @click.outside="editModalOpen = false" class="bg-[#181B28] border border-white/10 rounded-3xl max-w-lg w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-extrabold text-white">✏️ Cập Nhật / Thay Thế Tệp Âm Thanh</h3>
            <form :action="'{{ url('/admin/audio') }}/' + editAsset.id" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Tên âm thanh *</label>
                    <input type="text" name="title" :value="editAsset.title" required class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Phân loại</label>
                        <select name="category" :value="editAsset.category" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3 py-2 text-sm text-white">
                            <option value="sfx">SFX</option>
                            <option value="music">Music</option>
                            <option value="ambient">Ambient</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Phạm vi</label>
                        <select name="scope" :value="editAsset.scope" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3 py-2 text-sm text-white">
                            <option value="website">Website</option>
                            <option value="game">Game</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Event Key</label>
                        <input type="text" name="event_key" :value="editAsset.event_key" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Volume mặc định</label>
                        <input type="number" step="0.05" min="0" max="1" name="default_volume" :value="editAsset.default_volume" required class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Nguồn bản quyền</label>
                        <select name="source_type" :value="editAsset.source_type" class="w-full rounded-xl bg-[#202437] border border-white/10 px-3 py-2 text-sm text-white">
                            <option value="project_generated">Dự án tự sản xuất</option>
                            <option value="ai_generated">AI tạo ra</option>
                            <option value="public_domain">Public Domain</option>
                            <option value="licensed">Licensed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Giấy phép *</label>
                        <input type="text" name="license_name" :value="editAsset.license_name" required class="w-full rounded-xl bg-[#202437] border border-white/10 px-3.5 py-2 text-sm text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#00D4FF] mb-1">Thay thế tệp âm thanh mới (.mp3, .ogg, .wav - Để trống nếu giữ nguyên)</label>
                    <input type="file" name="audio_file" accept=".mp3,.ogg,.wav" class="text-xs text-[#A5ABC0]">
                </div>
                <div class="flex items-center gap-4">
                    <label class="inline-flex items-center gap-2 text-xs text-white">
                        <input type="checkbox" name="loop" value="1" :checked="editAsset.loop">
                        <span>🔁 Loop</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs text-emerald-400">
                        <input type="checkbox" name="is_active" value="1" :checked="editAsset.is_active">
                        <span>✅ Kích hoạt</span>
                    </label>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl bg-[#202437] text-xs font-bold text-[#A5ABC0]">Đóng</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-xs font-extrabold text-white">Cập Nhật</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

