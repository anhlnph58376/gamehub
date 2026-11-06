@extends('layouts.app')

@php $title = 'Giới Thiệu Về GameHub'; @endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12 space-y-8">
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-8 sm:p-10 space-y-6">
        <span class="px-3 py-1 rounded-lg bg-[#7C5CFF]/20 text-[#00D4FF] text-xs font-extrabold uppercase">Về Chúng Tôi</span>
        <h1 class="text-3xl font-black text-white">GameHub - Nền Tảng Cổng Game HTML5 Trực Tuyến Thế Hệ Mới</h1>
        <p class="text-[#A5ABC0] leading-relaxed">
            GameHub được xây dựng với sứ mệnh mang lại trải nghiệm giải trí HTML5 nhanh chóng, an toàn và hoàn toàn miễn phí cho cộng đồng game thủ Việt Nam. Không cần tải xuống hay cài đặt phần mềm nặng nề, bạn có thể thưởng thức ngay hàng trăm tựa game 2D hấp dẫn trên cả máy tính lẫn điện thoại thông minh.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-4">
            <div class="bg-[#202437] rounded-2xl p-5 border border-white/5">
                <h3 class="font-bold text-white mb-2">⚡ Chơi Tức Thì 60FPS</h3>
                <p class="text-xs text-[#A5ABC0]">Tối ưu hóa tài nguyên tĩnh giúp tải game chỉ trong vài giây trên mọi trình duyệt hiện đại.</p>
            </div>
            <div class="bg-[#202437] rounded-2xl p-5 border border-white/5">
                <h3 class="font-bold text-white mb-2">🛡️ Bảo Mật Iframe Sandbox</h3>
                <p class="text-xs text-[#A5ABC0]">Mọi gói game đều được quét mã độc tự động và chạy trong khung Sandbox cô lập hoàn toàn.</p>
            </div>
            <div class="bg-[#202437] rounded-2xl p-5 border border-white/5">
                <h3 class="font-bold text-white mb-2">📱 Hỗ Trợ Đa Nền Tảng</h3>
                <p class="text-xs text-[#A5ABC0]">Tương thích mượt mà cả bàn phím, chuột trên PC và màn hình cảm ứng trên Mobile/Tablet.</p>
            </div>
        </div>
    </div>
</div>
@endsection

