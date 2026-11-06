@extends('layouts.app')

@php $title = 'Xác Thực Mã Đăng Nhập Email'; @endphp

@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-8 shadow-2xl space-y-6">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-gradient-to-br from-[#7C5CFF] to-[#00D4FF] flex items-center justify-center font-black text-xl text-white shadow-lg shadow-[#7C5CFF]/30">
                📧
            </div>
            <h1 class="text-2xl font-black text-white">Xác Thực Đăng Nhập</h1>
            <p class="text-xs text-[#A5ABC0]">Nhập mã xác thực 6 chữ số đã được gửi tới hộp thư của bạn</p>
        </div>

        <form action="{{ route('login.email.verify.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1.5">Địa chỉ Email</label>
                <input type="email" name="email" value="{{ old('email', $email) }}" required placeholder="you@example.com" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:border-[#7C5CFF]">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1.5">Mã xác thực 6 chữ số (OTP)</label>
                <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus placeholder="123456" class="w-full rounded-xl bg-[#202437] border-2 border-[#7C5CFF]/50 px-4 py-3.5 text-center text-2xl font-black tracking-[0.5em] text-white focus:outline-none focus:border-[#00D4FF]">
                <p class="text-[11px] text-[#A5ABC0] mt-1.5">Mã có hiệu lực trong vòng 10 phút kể từ lúc yêu cầu.</p>
            </div>

            <label class="flex items-center gap-2 text-xs text-[#A5ABC0] cursor-pointer">
                <input type="checkbox" name="remember" value="1" class="rounded bg-[#202437] border-white/10 text-[#7C5CFF]">
                <span>Ghi nhớ đăng nhập trên thiết bị này</span>
            </label>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white font-extrabold text-sm shadow-lg shadow-[#7C5CFF]/30 hover:opacity-95 transition">
                Xác Nhận & Đăng Nhập
            </button>
        </form>

        <!-- Form gửi lại mã mới -->
        <div class="pt-3 border-t border-white/10 flex items-center justify-between text-xs">
            <form action="{{ route('login.email.send') }}" method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ old('email', $email) }}">
                <button type="submit" class="text-[#FFB84D] hover:underline font-bold">
                    ↻ Gửi lại mã mới
                </button>
            </form>
            <a href="{{ route('login') }}" class="text-[#00D4FF] hover:underline font-semibold">
                ← Quay lại Đăng nhập
            </a>
        </div>
    </div>
</div>
@endsection

