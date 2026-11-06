@extends('layouts.app')

@php $title = "Chọn Tài Khoản {$providerLabel}"; @endphp

@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-8 shadow-2xl space-y-6">
        <div class="text-center space-y-2">
            @if($provider === 'google')
                <div class="w-12 h-12 mx-auto rounded-2xl bg-white flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6" viewBox="0 0 24 24">
                        <path fill="#EA4335" d="M12 5c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 1.09 14.97 0 12 0 7.31 0 3.25 2.69 1.28 6.61l3.67 2.84C5.82 6.84 8.66 5 12 5z"/>
                        <path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.54-.19-2.27H12v4.51h6.47c-.29 1.48-1.14 2.73-2.4 3.58l3.56 2.76c2.08-1.92 3.86-4.74 3.86-8.58z"/>
                        <path fill="#FBBC05" d="M4.95 14.55A7.01 7.01 0 0 1 4.58 12c0-.89.16-1.75.37-2.55L1.28 6.61A11.96 11.96 0 0 0 0 12c0 1.92.46 3.74 1.28 5.39l3.67-2.84z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.56-2.76c-1.08.72-2.45 1.16-4.37 1.16-3.34 0-6.18-1.84-7.05-4.45l-3.67 2.84C3.25 21.31 7.31 24 12 24z"/>
                    </svg>
                </div>
            @else
                <div class="w-12 h-12 mx-auto rounded-2xl bg-[#1877F2] flex items-center justify-center text-white shadow-lg shadow-[#1877F2]/30">
                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </div>
            @endif
            <h1 class="text-xl font-black text-white">Tiếp tục với {{ $providerLabel }}</h1>
            <p class="text-xs text-[#A5ABC0]">Đăng nhập một chạm vào nền tảng <strong class="text-white">GameHub</strong></p>
        </div>

        <form action="{{ route('auth.social.callback', $provider) }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="state" value="{{ $state }}">

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1.5">
                    Địa chỉ Email {{ $provider === 'google' ? 'Gmail' : 'Facebook' }} của bạn
                </label>
                <input type="email" name="dev_email" required autofocus
                    value="{{ $provider === 'google' ? 'gamer@gmail.com' : 'gamer.fb@gmail.com' }}"
                    placeholder="tenban@gmail.com"
                    class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:border-[#7C5CFF]">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1.5">Tên hiển thị</label>
                <input type="text" name="dev_name" required
                    value="{{ $provider === 'google' ? 'Game Thủ Gmail' : 'Game Thủ Facebook' }}"
                    placeholder="Nhập tên hiển thị"
                    class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:border-[#7C5CFF]">
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white font-extrabold text-sm shadow-lg shadow-[#7C5CFF]/30 hover:opacity-95 transition">
                Tiếp Tục Đăng Nhập Bằng {{ $providerLabel }}
            </button>
        </form>

        <div class="p-3.5 rounded-2xl bg-[#10121B] border border-white/10 text-[11px] text-[#A5ABC0] space-y-1.5">
            <div class="font-bold text-[#00D4FF]">ℹ️ Chế độ OAuth Local Development:</div>
            <p>Khi triển khai trên Web thực tế và điền <code class="text-white">{{ strtoupper($provider) }}_CLIENT_ID</code> & <code class="text-white">{{ strtoupper($provider) }}_CLIENT_SECRET</code> vào <code class="text-white">.env</code>, nút này sẽ tự động chuyển hướng trực tiếp sang cổng ủy quyền chính thức của {{ $providerLabel }}.</p>
            <p class="text-[10px] text-[#6E7691] break-all">Callback URI: {{ route('auth.social.callback', $provider) }}</p>
        </div>

        <div class="text-center pt-1">
            <a href="{{ route('login') }}" class="text-xs text-[#00D4FF] hover:underline font-semibold">
                ← Quay lại trang Đăng nhập
            </a>
        </div>
    </div>
</div>
@endsection

