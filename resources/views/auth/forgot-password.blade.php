@extends('layouts.app')

@php $title = 'Quên Mật Khẩu'; @endphp

@section('content')
<div class="max-w-md mx-auto px-4 py-14">
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-8 shadow-2xl space-y-6">
        <div class="text-center space-y-2">
            <h1 class="text-2xl font-black text-white">Khôi Phục Mật Khẩu</h1>
            <p class="text-xs text-[#A5ABC0]">Nhập email đã đăng ký để nhận liên kết đặt lại mật khẩu</p>
        </div>

        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1.5">Địa chỉ Email</label>
                <input type="email" name="email" required placeholder="you@example.com" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:border-[#7C5CFF]">
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-[#7C5CFF] text-white font-extrabold text-sm">
                Gửi Hướng Dẫn Khôi Phục
            </button>
        </form>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-xs text-[#00D4FF] hover:underline">← Quay lại trang Đăng nhập</a>
        </div>
    </div>
</div>
@endsection

