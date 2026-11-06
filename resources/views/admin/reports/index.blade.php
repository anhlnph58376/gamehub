@extends('layouts.admin')

@php $headerTitle = 'Xử Lý Báo Cáo Lỗi Game & Vi Phạm'; @endphp

@section('content')
<div class="bg-[#181B28] border border-white/10 rounded-2xl overflow-hidden">
    <table class="w-full text-left text-sm">
        <thead class="bg-[#202437] text-xs uppercase text-[#A5ABC0]">
            <tr>
                <th class="px-5 py-3.5">Trò chơi</th>
                <th class="px-5 py-3.5">Loại báo cáo</th>
                <th class="px-5 py-3.5">Mô tả chi tiết</th>
                <th class="px-5 py-3.5">Người gửi</th>
                <th class="px-5 py-3.5">Xử lý</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @forelse($reports as $rep)
                <tr>
                    <td class="px-5 py-4 font-bold text-white">{{ $rep->game->title ?? 'N/A' }}</td>
                    <td class="px-5 py-4">
                        <span class="px-2.5 py-1 rounded-lg bg-rose-500/20 text-rose-400 text-xs font-bold uppercase">{{ $rep->type }}</span>
                    </td>
                    <td class="px-5 py-4 text-xs text-[#A5ABC0]">{{ $rep->description }}</td>
                    <td class="px-5 py-4 text-xs text-[#A5ABC0]">{{ $rep->user->name ?? 'Khách' }}</td>
                    <td class="px-5 py-4">
                        <form action="{{ route('admin.reports.resolve', $rep) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            @method('PUT')
                            <select name="status" class="rounded-lg bg-[#202437] border border-white/10 px-2.5 py-1 text-xs text-white">
                                <option value="pending" @selected($rep->status === 'pending')>Chờ xử lý</option>
                                <option value="reviewed" @selected($rep->status === 'reviewed')>Đang kiểm tra</option>
                                <option value="resolved" @selected($rep->status === 'resolved')>Đã khắc phục</option>
                                <option value="dismissed" @selected($rep->status === 'dismissed')>Bỏ qua</option>
                            </select>
                            <input type="text" name="admin_note" value="{{ $rep->admin_note }}" placeholder="Ghi chú xử lý..." class="rounded-lg bg-[#202437] border border-white/10 px-2.5 py-1 text-xs text-white">
                            <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-500 text-[#10121B] text-xs font-extrabold">Cập nhật</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-[#A5ABC0]">Không có báo cáo lỗi nào.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $reports->links() }}</div>
@endsection

