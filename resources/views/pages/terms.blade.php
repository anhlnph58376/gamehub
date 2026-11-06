@extends('layouts.app')

@php $title = 'Điều Khoản Sử Dụng'; @endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-8 space-y-5 text-sm text-[#A5ABC0] leading-relaxed">
        <h1 class="text-2xl sm:text-3xl font-black text-white">📜 Điều Khoản Sử Dụng Dịch Vụ</h1>
        <p>Khi tham gia giải trí tại cổng game GameHub, người dùng đồng ý tuân thủ các quy định cộng đồng sau:</p>
        <ul class="list-disc list-inside space-y-2 text-white/90">
            <li>Không sử dụng ngôn từ thiếu văn hóa, spam hoặc xúc phạm người chơi khác trong khu vực bình luận & đánh giá game.</li>
            <li>Không can thiệp trái phép vào hệ thống điểm số hoặc khai thác lỗ hổng kỹ thuật.</li>
            <li>Ban quản trị có quyền ẩn bình luận vi phạm hoặc khóa tài khoản nếu phát hiện hành vi gian lận.</li>
        </ul>
    </div>
</div>
@endsection

