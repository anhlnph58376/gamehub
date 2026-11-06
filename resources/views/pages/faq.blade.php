@extends('layouts.app')

@php $title = 'Câu Hỏi Thường Gặp (FAQ)'; @endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12 space-y-6">
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-8 space-y-6">
        <h1 class="text-2xl sm:text-3xl font-black text-white">❓ Câu Hỏi Thường Gặp (FAQ)</h1>

        <div class="space-y-4">
            <div class="bg-[#202437] rounded-2xl p-5 border border-white/5">
                <h3 class="font-bold text-white">1. Chơi game trên GameHub có mất phí hay cần cài đặt không?</h3>
                <p class="text-sm text-[#A5ABC0] mt-2">Hoàn toàn miễn phí 100%! Tất cả trò chơi đều chạy bằng công nghệ HTML5 chuẩn quốc tế trực tiếp ngay trên trình duyệt web của bạn.</p>
            </div>
            <div class="bg-[#202437] rounded-2xl p-5 border border-white/5">
                <h3 class="font-bold text-white">2. Làm sao để lưu điểm số cao và mở khóa Huy hiệu Thành tích?</h3>
                <p class="text-sm text-[#A5ABC0] mt-2">Bạn chỉ cần đăng ký một tài khoản miễn phí và đăng nhập trước khi chơi. Hệ thống sẽ tự động ghi nhận điểm số và trao huy hiệu vào Hồ sơ cá nhân.</p>
            </div>
            <div class="bg-[#202437] rounded-2xl p-5 border border-white/5">
                <h3 class="font-bold text-white">3. Tôi có thể chơi trên điện thoại di động (iOS / Android) không?</h3>
                <p class="text-sm text-[#A5ABC0] mt-2">Có! Hầu hết các trò chơi trên GameHub đều hỗ trợ điều khiển cảm ứng (Touch) trực tiếp trên màn hình điện thoại và máy tính bảng.</p>
            </div>
        </div>
    </div>
</div>
@endsection

