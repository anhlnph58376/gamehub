<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Banner;
use App\Models\Game;
use App\Models\HomepageSection;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminCmsController extends Controller
{
    public function index(): View
    {
        $banners = Banner::with('game')->orderBy('position')->get();
        $sections = HomepageSection::orderBy('position')->get();
        $announcements = Announcement::latest()->get();
        $games = Game::published()->orderBy('title')->get(['id', 'title']);

        return view('admin.cms.index', compact('banners', 'sections', 'announcements', 'games'));
    }

    public function storeBanner(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'subtitle' => ['nullable', 'string', 'max:300'],
            'badge_text' => ['nullable', 'string', 'max:50'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'game_id' => ['nullable', 'exists:games,id'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề Banner.',
        ]);

        $banner = Banner::create([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'badge_text' => $validated['badge_text'] ?? 'SIÊU PHẨM HOT',
            'button_text' => $validated['button_text'] ?: 'Chơi Ngay',
            'game_id' => $validated['game_id'] ?? null,
            'link_url' => $validated['link_url'] ?? null,
            'position' => (int) ($validated['position'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        Cache::forget('home_banners');
        AuditLog::record('banner.create', "Thêm Banner trang chủ '{$banner->title}'.", Banner::class, $banner->id);

        return back()->with('success', 'Đã thêm Banner trang chủ thành công.');
    }

    public function deleteBanner(Banner $banner): RedirectResponse
    {
        $title = $banner->title;
        $banner->delete();
        Cache::forget('home_banners');

        AuditLog::record('banner.delete', "Xóa Banner '{$title}'.", Banner::class);

        return back()->with('success', 'Đã xóa Banner.');
    }

    public function updateSection(Request $request, HomepageSection $section): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'position' => ['required', 'integer', 'min:0'],
            'item_limit' => ['required', 'integer', 'min:1', 'max:24'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $section->update([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? $section->subtitle,
            'position' => (int) $validated['position'],
            'item_limit' => (int) $validated['item_limit'],
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLog::record('homepage_section.update', "Cập nhật khu vực trang chủ '{$section->title}'.", HomepageSection::class, $section->id);

        return back()->with('success', "Đã cập nhật khu vực '{$section->title}'.");
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string', 'max:1000'],
            'type' => ['required', 'in:info,warning,success'],
        ]);

        Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Đã đăng thông báo hệ thống.');
    }

    public function deleteAnnouncement(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('success', 'Đã xóa thông báo.');
    }

    public function settings(): View
    {
        $settings = Setting::all()->keyBy('key');

        return view('admin.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $fields = [
            'site_name' => 'Tên cổng game',
            'site_tagline' => 'Khẩu hiệu (Tagline)',
            'site_description' => 'Mô tả SEO mặc định',
            'contact_email' => 'Email hỗ trợ',
            'footer_copyright' => 'Bản quyền chân trang',
            'sandbox_notice' => 'Thông báo bảo mật Sandbox',
        ];

        foreach ($fields as $key => $label) {
            if ($request->has($key)) {
                Setting::setValue($key, (string) $request->input($key), 'general', $label);
            }
        }

        AuditLog::record('settings.update', 'Cập nhật cấu hình hệ thống GameHub.');

        return back()->with('success', 'Đã lưu cài đặt hệ thống thành công.');
    }

    public function auditLogs(): View
    {
        $logs = AuditLog::with('user')->latest()->paginate(25);

        return view('admin.audit-logs.index', compact('logs'));
    }
}

