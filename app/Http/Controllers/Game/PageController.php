<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'name.required' => 'Vui lòng nhập họ và tên của bạn.',
            'email.required' => 'Vui lòng nhập địa chỉ email liên hệ.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'subject.required' => 'Vui lòng nhập tiêu đề liên hệ.',
            'message.required' => 'Vui lòng nhập nội dung tin nhắn.',
            'message.min' => 'Nội dung tin nhắn cần ít nhất 10 ký tự.',
        ]);

        return back()->with('success', 'Cảm ơn bạn đã liên hệ với GameHub! Đội ngũ hỗ trợ sẽ phản hồi sớm nhất.');
    }

    public function faq(): View
    {
        return view('pages.faq');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function terms(): View
    {
        return view('pages.terms');
    }
}

