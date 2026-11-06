@extends('layouts.app')

@php $title = 'Đăng Ký Tài Khoản'; @endphp

@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-8 shadow-2xl space-y-6">
        <div class="text-center space-y-2">
            <h1 class="text-2xl font-black text-white">Tạo Tài Khoản GameHub</h1>
            <p class="text-xs text-[#A5ABC0]">Nhận ngay 10 điểm thành tích khởi đầu khi đăng ký mới</p>
        </div>

        <form action="{{ route('register.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1.5">Tên hiển thị / Biệt danh</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="VD: ProGamer2026" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:border-[#7C5CFF]">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1.5">Địa chỉ Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:border-[#7C5CFF]">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1.5">Mật khẩu (tối thiểu 8 ký tự)</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:border-[#7C5CFF]">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1.5">Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:border-[#7C5CFF]">
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white font-extrabold text-sm shadow-lg shadow-[#7C5CFF]/30 hover:opacity-95 transition">
                Hoàn Tất Đăng Ký
            </button>
        </form>

        <p class="text-center text-xs text-[#A5ABC0] pt-2 border-t border-white/10">
            Đã có tài khoản? <a href="{{ route('login') }}" class="text-[#00D4FF] font-bold hover:underline">Đăng nhập tại đây</a>
        </p>
    </div>
</div>
@endsection

