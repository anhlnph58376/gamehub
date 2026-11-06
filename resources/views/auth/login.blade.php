@extends('layouts.app')

@php $title = 'Đăng Nhập Tài Khoản'; @endphp

@section('content')
<div class="max-w-md mx-auto px-4 py-14">
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-8 shadow-2xl space-y-6">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-gradient-to-br from-[#7C5CFF] to-[#00D4FF] flex items-center justify-center font-black text-xl text-white shadow-lg shadow-[#7C5CFF]/30">
                G
            </div>
            <h1 class="text-2xl font-black text-white">Đăng Nhập GameHub</h1>
            <p class="text-xs text-[#A5ABC0]">Lưu trữ điểm cao, lịch sử chơi và danh sách game yêu thích</p>
        </div>

        <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1.5">Địa chỉ Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="you@example.com" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:border-[#7C5CFF]">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-bold text-[#A5ABC0]">Mật khẩu</label>
                    <a href="{{ route('password.request') }}" class="text-xs text-[#00D4FF] hover:underline">Quên mật khẩu?</a>
                </div>
                <input type="password" name="password" required placeholder="••••••••" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:border-[#7C5CFF]">
            </div>

            <label class="flex items-center gap-2 text-xs text-[#A5ABC0] cursor-pointer">
                <input type="checkbox" name="remember" value="1" class="rounded bg-[#202437] border-white/10 text-[#7C5CFF]">
                <span>Ghi nhớ đăng nhập trên thiết bị này</span>
            </label>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white font-extrabold text-sm shadow-lg shadow-[#7C5CFF]/30 hover:opacity-95 transition">
                Đăng Nhập Ngay
            </button>
        </form>

        <p class="text-center text-xs text-[#A5ABC0] pt-2 border-t border-white/10">
            Chưa có tài khoản? <a href="{{ route('register') }}" class="text-[#00D4FF] font-bold hover:underline">Đăng ký miễn phí</a>
        </p>
    </div>
</div>
@endsection

