@extends('layouts.admin')

@php $headerTitle = 'Quản Lý Người Dùng & Phân Quyền RBAC'; @endphp

@section('content')
<div class="space-y-6">
    <div class="bg-[#181B28] border border-white/10 rounded-2xl p-4">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên hoặc email..." class="rounded-xl bg-[#202437] border border-white/10 px-4 py-2 text-sm text-white w-72">
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#7C5CFF] text-white text-xs font-bold">Tìm kiếm</button>
        </form>
    </div>

    <div class="bg-[#181B28] border border-white/10 rounded-2xl overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-[#202437] text-xs uppercase text-[#A5ABC0]">
                <tr>
                    <th class="px-5 py-3.5">Thành viên</th>
                    <th class="px-5 py-3.5">Vai trò (Roles)</th>
                    <th class="px-5 py-3.5">Hoạt động</th>
                    <th class="px-5 py-3.5">Trạng thái</th>
                    <th class="px-5 py-3.5 text-right">Quản lý</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @foreach($users as $user)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-bold text-white">{{ $user->name }}</p>
                            <p class="text-xs text-[#A5ABC0]">{{ $user->email }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <form action="{{ route('admin.users.roles.update', $user) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <select name="role_ids[]" class="rounded-lg bg-[#202437] border border-white/10 px-2.5 py-1 text-xs text-white">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" @selected($user->roles->contains('id', $role->id))>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-[#7C5CFF]/30 text-[#00D4FF] text-xs font-bold">Lưu</button>
                            </form>
                        </td>
                        <td class="px-5 py-4 text-xs text-[#A5ABC0]">
                            {{ $user->histories_count }} lượt chơi • {{ $user->reviews_count }} đánh giá
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $user->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                {{ $user->is_active ? 'Đang hoạt động' : 'Đã khóa' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if(!$user->isSuperAdmin() && $user->id !== auth()->id())
                                <form action="{{ route('admin.users.toggle-lock', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $user->is_active ? 'bg-rose-500/20 text-rose-400' : 'bg-emerald-500/20 text-emerald-400' }}">
                                        {{ $user->is_active ? '🔒 Khóa' : '🔓 Mở khóa' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div>{{ $users->links() }}</div>
</div>
@endsection

