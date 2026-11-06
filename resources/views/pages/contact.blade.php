@extends('layouts.app')

@php $title = 'Liên Hệ & Hỗ Trợ'; @endphp

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
    <div class="bg-[#181B28] border border-white/10 rounded-3xl p-8 sm:p-10 space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white">📬 Liên Hệ Với Ban Quản Trị GameHub</h1>
            <p class="text-sm text-[#A5ABC0] mt-1">Gửi câu hỏi, đề xuất hợp tác phát hành game HTML5 hoặc báo cáo sự cố kỹ thuật</p>
        </div>

        <form action="{{ route('pages.contact.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Họ và tên của bạn</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Địa chỉ Email</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Tiêu đề liên hệ</label>
                <input type="text" name="subject" value="{{ old('subject') }}" required placeholder="VD: Đề xuất tích hợp game HTML5 mới..." class="w-full rounded-xl bg-[#202437] border border-white/10 px-4 py-3 text-sm text-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#A5ABC0] mb-1">Nội dung chi tiết</label>
                <textarea name="message" rows="5" required placeholder="Nhập nội dung tin nhắn của bạn..." class="w-full rounded-xl bg-[#202437] border border-white/10 p-4 text-sm text-white">{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="px-6 py-3.5 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-white font-extrabold text-sm">
                Gửi Tin Nhắn Liên Hệ
            </button>
        </form>
    </div>
</div>
@endsection

