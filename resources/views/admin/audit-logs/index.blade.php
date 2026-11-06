@extends('layouts.admin')

@php $headerTitle = 'Nhật Ký Hoạt Động Hệ Thống (Audit Logs)'; @endphp

@section('content')
<div class="bg-[#181B28] border border-white/10 rounded-2xl overflow-hidden">
    <table class="w-full text-left text-sm">
        <thead class="bg-[#202437] text-xs uppercase text-[#A5ABC0]">
            <tr>
                <th class="px-5 py-3.5">Thời gian</th>
                <th class="px-5 py-3.5">Người thực hiện</th>
                <th class="px-5 py-3.5">Hành động</th>
                <th class="px-5 py-3.5">Mô tả chi tiết</th>
                <th class="px-5 py-3.5">Địa chỉ IP</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @forelse($logs as $log)
                <tr>
                    <td class="px-5 py-3.5 text-xs text-[#A5ABC0]">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td class="px-5 py-3.5 font-bold text-white">{{ $log->user->name ?? 'Hệ thống' }}</td>
                    <td class="px-5 py-3.5">
                        <span class="px-2.5 py-0.5 rounded bg-[#202437] text-[#00D4FF] text-xs font-mono">{{ $log->action }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-[#A5ABC0]">{{ $log->description }}</td>
                    <td class="px-5 py-3.5 text-xs font-mono text-[#A5ABC0]">{{ $log->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-[#A5ABC0]">Chưa có bản ghi Audit Log nào.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection

