@extends('layouts.admin')

@php $headerTitle = 'Quản Lý Danh Mục Trò Chơi'; @endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Add Category Form -->
    <div class="lg:col-span-4 bg-[#181B28] border border-white/10 rounded-2xl p-6 h-fit space-y-4">
        <h2 class="text-base font-extrabold text-white">+ Thêm Danh Mục Mới</h2>
        <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Tên danh mục *</label>
                <input type="text" name="name" required placeholder="VD: Hành động, Giải đố..." class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Thứ tự hiển thị</label>
                <input type="number" name="position" value="0" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2 text-sm text-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Mô tả danh mục</label>
                <textarea name="description" rows="3" class="w-full rounded-xl bg-[#202437] border border-white/10 p-3 text-sm text-white"></textarea>
            </div>
            <label class="flex items-center gap-2 text-xs text-white">
                <input type="checkbox" name="is_active" value="1" checked> Kích hoạt hiển thị
            </label>
            <button type="submit" class="w-full py-2.5 rounded-xl bg-[#7C5CFF] text-white text-xs font-extrabold uppercase">Thêm Danh Mục</button>
        </form>
    </div>

    <!-- Categories List -->
    <div class="lg:col-span-8 bg-[#181B28] border border-white/10 rounded-2xl overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-[#202437] text-xs uppercase text-[#A5ABC0]">
                <tr>
                    <th class="px-5 py-3.5">Tên danh mục</th>
                    <th class="px-5 py-3.5">Slug</th>
                    <th class="px-5 py-3.5">Số game</th>
                    <th class="px-5 py-3.5">Thứ tự</th>
                    <th class="px-5 py-3.5">Trạng thái</th>
                    <th class="px-5 py-3.5 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @foreach($categories as $cat)
                    <tr>
                        <td class="px-5 py-4 font-bold text-white">{{ $cat->name }}</td>
                        <td class="px-5 py-4 text-xs text-[#A5ABC0]">/{{ $cat->slug }}</td>
                        <td class="px-5 py-4 font-bold text-[#00D4FF]">{{ $cat->games_count }}</td>
                        <td class="px-5 py-4 text-[#A5ABC0]">{{ $cat->position }}</td>
                        <td class="px-5 py-4">
                            <span class="px-2 py-0.5 rounded text-xs font-bold {{ $cat->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                {{ $cat->is_active ? 'Hiển thị' : 'Đang ẩn' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-rose-400 hover:underline">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

