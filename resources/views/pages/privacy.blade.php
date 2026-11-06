@extends('layouts.app')

@php $title = 'Chính Sách Bảo Mật'; @endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-8 space-y-5 text-sm text-[#A5ABC0] leading-relaxed">
        <h1 class="text-2xl sm:text-3xl font-black text-white">🔒 Chính Sách Bảo Mật Thông Tin</h1>
        <p>GameHub cam kết bảo vệ quyền riêng tư và dữ liệu cá nhân của mọi người chơi:</p>
        <ul class="list-disc list-inside space-y-2 text-white/90">
            <li>Mật khẩu người dùng được mã hóa một chiều bằng thuật toán Bcrypt tiêu chuẩn cao.</li>
            <li>Các trò chơi HTML5 được nhúng trong môi trường Iframe Sandbox cô lập, không thể truy cập cookie hay thông tin phiên đăng nhập của bạn.</li>
            <li>Thông tin email chỉ được sử dụng để xác thực tài khoản và hỗ trợ khôi phục mật khẩu theo yêu cầu của người dùng.</li>
        </ul>
    </div>
</div>
@endsection

