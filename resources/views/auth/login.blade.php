@extends('layouts.app')

@php $title = 'Đăng Nhập Tài Khoản'; @endphp

@section('content')
<div class="max-w-md mx-auto px-4 py-10" x-data="{ tab: '{{ old('login_tab', request('tab', 'password')) }}' }">
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-8 shadow-2xl space-y-6">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-gradient-to-br from-[#7C5CFF] to-[#00D4FF] flex items-center justify-center font-black text-xl text-white shadow-lg shadow-[#7C5CFF]/30">
                G
            </div>
            <h1 class="text-2xl font-black text-white">Đăng Nhập GameHub</h1>
            <p class="text-xs text-[#A5ABC0]">Lưu trữ điểm cao, lịch sử chơi và danh sách game yêu thích</p>
        </div>

        <!-- Nút Đăng nhập nhanh bằng Google (Gmail) & Facebook -->
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('auth.social.redirect', 'google') }}"
               class="flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl bg-white hover:bg-gray-100 text-[#10121B] font-extrabold text-xs shadow-md transition transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                    <path fill="#EA4335" d="M12 5c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 1.09 14.97 0 12 0 7.31 0 3.25 2.69 1.28 6.61l3.67 2.84C5.82 6.84 8.66 5 12 5z"/>
                    <path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.54-.19-2.27H12v4.51h6.47c-.29 1.48-1.14 2.73-2.4 3.58l3.56 2.76c2.08-1.92 3.86-4.74 3.86-8.58z"/>
                    <path fill="#FBBC05" d="M4.95 14.55A7.01 7.01 0 0 1 4.58 12c0-.89.16-1.75.37-2.55L1.28 6.61A11.96 11.96 0 0 0 0 12c0 1.92.46 3.74 1.28 5.39l3.67-2.84z"/>
                    <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.56-2.76c-1.08.72-2.45 1.16-4.37 1.16-3.34 0-6.18-1.84-7.05-4.45l-3.67 2.84C3.25 21.31 7.31 24 12 24z"/>
                </svg>
                <span>Google (Gmail)</span>
            </a>

            <a href="{{ route('auth.social.redirect', 'facebook') }}"
               class="flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl bg-[#1877F2] hover:bg-[#166FE5] text-white font-extrabold text-xs shadow-md shadow-[#1877F2]/25 transition transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 shrink-0 fill-current" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                <span>Facebook</span>
            </a>
        </div>

        <div class="relative flex py-1 items-center">
            <div class="grow border-t border-white/10"></div>
            <span class="shrink mx-3 text-[11px] font-bold uppercase tracking-wider text-[#A5ABC0]">Hoặc đăng nhập bằng Email</span>
            <div class="grow border-t border-white/10"></div>
        </div>

        <!-- Chuyển đổi phương thức đăng nhập: Mật khẩu hoặc Mã Email OTP -->
        <div class="grid grid-cols-2 gap-1.5 p-1.5 rounded-2xl bg-[#10121B] border border-white/10">
            <button type="button"
                @click="tab = 'password'"
                :class="tab === 'password' ? 'bg-[#7C5CFF] text-white shadow-lg shadow-[#7C5CFF]/25' : 'text-[#A5ABC0] hover:text-white'"
                class="py-2.5 px-3 rounded-xl text-xs font-extrabold transition flex items-center justify-center gap-1.5">
                <span>🔑</span>
                <span>Bằng Mật Khẩu</span>
            </button>
            <button type="button"
                @click="tab = 'email_otp'"
                :class="tab === 'email_otp' ? 'bg-[#7C5CFF] text-white shadow-lg shadow-[#7C5CFF]/25' : 'text-[#A5ABC0] hover:text-white'"
                class="py-2.5 px-3 rounded-xl text-xs font-extrabold transition flex items-center justify-center gap-1.5">
                <span>📧</span>
                <span>Bằng Mã Email</span>
            </button>
        </div>

        <!-- TAB 1: Đăng nhập bằng Email & Mật khẩu -->
        <form x-show="tab === 'password'" action="{{ route('login.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1.5">Địa chỉ Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="you@example.com" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:border-[#7C5CFF]">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-bold text-[#A5ABC0]">Mật khẩu</label>
                    <a href="{{ route('password.request') }}" class="text-xs text-[#00D4FF] hover:underline font-semibold">Quên mật khẩu?</a>
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

        <!-- TAB 2: Đăng nhập nhanh bằng Mã xác thực gửi về Email (OTP) -->
        <form x-show="tab === 'email_otp'" x-cloak action="{{ route('login.email.send') }}" method="POST" class="space-y-4">
            @csrf
            <div class="p-3.5 rounded-2xl bg-[#202437]/70 border border-[#00D4FF]/20 text-xs text-[#A5ABC0] leading-relaxed">
                Nhập địa chỉ Email đã đăng ký của bạn. Hệ thống sẽ gửi một <strong class="text-white">mã xác thực 6 chữ số</strong> trực tiếp vào hộp thư để bạn đăng nhập ngay mà không cần nhập mật khẩu.
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1.5">Địa chỉ Email nhận mã</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:border-[#00D4FF]">
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white font-extrabold text-sm shadow-lg shadow-[#7C5CFF]/30 hover:opacity-95 transition flex items-center justify-center gap-2">
                <span>📨</span>
                <span>Gửi Mã Đăng Nhập Về Email</span>
            </button>

            <div class="text-center pt-1">
                <a href="{{ route('login.email.verify.form') }}" class="text-xs text-[#00D4FF] hover:underline font-semibold">
                    Đã nhận được mã 6 chữ số? Nhập mã tại đây →
                </a>
            </div>
        </form>

        <p class="text-center text-xs text-[#A5ABC0] pt-2 border-t border-white/10">
            Chưa có tài khoản? <a href="{{ route('register') }}" class="text-[#00D4FF] font-bold hover:underline">Đăng ký miễn phí</a>
        </p>
    </div>
</div>
@endsection
