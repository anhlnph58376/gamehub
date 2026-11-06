@extends('layouts.admin')

@php $headerTitle = 'Cài Đặt Hệ Thống (System Settings)'; @endphp

@section('content')
<div class="max-w-3xl bg-[#181B28] border border-white/10 rounded-2xl p-6">
    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Tên cổng game (Site Name)</label>
            <input type="text" name="site_name" value="{{ $settings['site_name']->value ?? 'GameHub' }}" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Khẩu hiệu (Tagline)</label>
            <input type="text" name="site_tagline" value="{{ $settings['site_tagline']->value ?? 'Cổng Game HTML5 Trực Tuyến Miễn Phí' }}" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Mô tả SEO mặc định</label>
            <textarea name="site_description" rows="3" class="w-full rounded-xl bg-[#202437] border border-white/10 p-3.5 text-sm text-white">{{ $settings['site_description']->value ?? 'Chơi hàng trăm tựa game HTML5 2D miễn phí tốc độ cao ngay trên trình duyệt PC và Mobile tại GameHub.' }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Email hỗ trợ</label>
            <input type="email" name="contact_email" value="{{ $settings['contact_email']->value ?? 'support@gamehub.local' }}" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Dòng bản quyền Footer</label>
            <input type="text" name="footer_copyright" value="{{ $settings['footer_copyright']->value ?? '© 2026 GameHub Portal. Bảo lưu mọi quyền.' }}" class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white">
        </div>
        <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#7C5CFF] text-white text-xs font-extrabold uppercase">Lưu Cài Đặt</button>
    </form>
</div>
@endsection

