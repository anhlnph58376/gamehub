@extends('layouts.admin')

@php $headerTitle = 'Quản Lý Đánh Giá & Bình Luận (Reviews)'; @endphp

@section('content')
<div class="bg-[#181B28] border border-white/10 rounded-2xl overflow-hidden">
    <table class="w-full text-left text-sm">
        <thead class="bg-[#202437] text-xs uppercase text-[#A5ABC0]">
            <tr>
                <th class="px-5 py-3.5">Người chơi</th>
                <th class="px-5 py-3.5">Trò chơi</th>
                <th class="px-5 py-3.5">Điểm sao & Bình luận</th>
                <th class="px-5 py-3.5">Trạng thái</th>
                <th class="px-5 py-3.5 text-right">Thao tác</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @forelse($reviews as $rev)
                <tr>
                    <td class="px-5 py-4 font-bold text-white">{{ $rev->user->name ?? 'Ẩn danh' }}</td>
                    <td class="px-5 py-4 text-[#00D4FF] font-semibold">{{ $rev->game->title ?? 'Đã xóa' }}</td>
                    <td class="px-5 py-4">
                        <span class="text-[#FFB84D] font-bold">{{ str_repeat('★', $rev->rating) }}</span>
                        <p class="text-xs text-[#A5ABC0] mt-1">{{ $rev->comment }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <form action="{{ route('admin.reviews.status', $rev) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            @method('PUT')
                            <select name="status" class="rounded-lg bg-[#202437] border border-white/10 px-2.5 py-1 text-xs text-white">
                                <option value="approved" @selected($rev->status === 'approved')>Hiển thị (Approved)</option>
                                <option value="hidden" @selected($rev->status === 'hidden')>Ẩn (Hidden)</option>
                                <option value="rejected" @selected($rev->status === 'rejected')>Từ chối (Rejected)</option>
                            </select>
                            <button type="submit" class="px-2.5 py-1 rounded bg-[#7C5CFF] text-white text-xs font-bold">Lưu</button>
                        </form>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <form action="{{ route('admin.reviews.destroy', $rev) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-400 hover:underline">Xóa vĩnh viễn</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-[#A5ABC0]">Chưa có đánh giá nào.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $reviews->links() }}</div>
@endsection

