@extends('layouts.admin')

@php $headerTitle = 'Quản Lý Nhãn Phân Loại (Tags)'; @endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <div class="lg:col-span-4 bg-[#181B28] border border-white/10 rounded-2xl p-6 h-fit space-y-4">
        <h2 class="text-base font-extrabold text-white">+ Thêm Tag Mới</h2>
        <form action="{{ route('admin.tags.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Tên Tag *</label>
                <input type="text" name="name" required placeholder="VD: 2D, Pixel Art, Không gian..." class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Mô tả</label>
                <input type="text" name="description" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2 text-sm text-white">
            </div>
            <button type="submit" class="w-full py-2.5 rounded-xl bg-[#7C5CFF] text-white text-xs font-extrabold uppercase">Tạo Tag</button>
        </form>
    </div>

    <div class="lg:col-span-8 bg-[#181B28] border border-white/10 rounded-2xl overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-[#202437] text-xs uppercase text-[#A5ABC0]">
                <tr>
                    <th class="px-5 py-3.5">Tag</th>
                    <th class="px-5 py-3.5">Slug</th>
                    <th class="px-5 py-3.5">Số game gắn nhãn</th>
                    <th class="px-5 py-3.5 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @foreach($tags as $tag)
                    <tr>
                        <td class="px-5 py-4 font-bold text-white">#{{ $tag->name }}</td>
                        <td class="px-5 py-4 text-xs text-[#A5ABC0]">{{ $tag->slug }}</td>
                        <td class="px-5 py-4 font-bold text-[#00D4FF]">{{ $tag->games_count }}</td>
                        <td class="px-5 py-4 text-right">
                            <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST" class="inline">
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

