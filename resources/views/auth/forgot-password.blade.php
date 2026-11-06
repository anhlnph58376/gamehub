@extends('layouts.app')

@php $title = 'Quên Mật Khẩu'; @endphp

@section('content')
<div class="max-w-md mx-auto px-4 py-14">
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-8 shadow-2xl space-y-6">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-gradient-to-br from-[#7C5CFF] to-[#00D4FF] flex items-center justify-center font-black text-xl text-white shadow-lg shadow-[#7C5CFF]/30">
                🔐
            </div>
            <h1 class="text-2xl font-black text-white">Khôi Phục Mật Khẩu</h1>
            <p class="text-xs text-[#A5ABC0]">Nhập địa chỉ email đã đăng ký để nhận mã xác thực 6 chữ số gửi về hộp thư của bạn</p>
        </div>

        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1.5">Địa chỉ Email đã đăng ký</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="you@example.com" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:border-[#7C5CFF]">
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white font-extrabold text-sm shadow-lg shadow-[#7C5CFF]/30 hover:opacity-95 transition flex items-center justify-center gap-2">
                <span>📨</span>
                <span>Gửi Mã Xác Thực Về Email</span>
            </button>
        </form>

        <div class="pt-3 border-t border-white/10 flex items-center justify-between text-xs">
            <a href="{{ route('password.reset.form') }}" class="text-[#FFB84D] hover:underline font-bold">
                Đã có mã 6 số? Đặt lại mật khẩu →
            </a>
            <a href="{{ route('login') }}" class="text-[#00D4FF] hover:underline font-semibold">
                ← Đăng nhập
            </a>
        </div>
    </div>
</div>
@endsection
