<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\Auth\SocialOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class SocialAuthController extends Controller
{
    /**
     * Điều hướng người dùng tới trang ủy quyền OAuth 2.0 của Google (Gmail) hoặc Facebook.
     */
    public function redirect(Request $request, string $provider, SocialOAuthService $socialService): RedirectResponse|View
    {
        if (!$socialService->isSupported($provider)) {
            abort(404);
        }

        $state = Str::random(40);
        $request->session()->put('social_oauth_state', $state);

        // Nếu đã cấu hình GOOGLE_CLIENT_ID / FACEBOOK_CLIENT_ID thực tế -> Chuyển hướng tới Google / Facebook OAuth
        if ($socialService->isConfigured($provider)) {
            return redirect()->away($socialService->buildAuthorizationUrl($provider, $state));
        }

        // Trên môi trường Local/Testing chưa khai báo Client ID: Hiển thị cổng giả lập chọn tài khoản OAuth để test ngay
        if (app()->environment('local', 'testing')) {
            return view('auth.social-dev-chooser', [
                'provider' => $provider,
                'providerLabel' => $provider === 'google' ? 'Google (Gmail)' : 'Facebook',
                'state' => $state,
            ]);
        }

        $envKey = $provider === 'google' ? 'GOOGLE_CLIENT_ID & GOOGLE_CLIENT_SECRET' : 'FACEBOOK_CLIENT_ID & FACEBOOK_CLIENT_SECRET';

        return redirect()->route('login')
            ->with('error', "Cổng đăng nhập " . ucfirst($provider) . " chưa được cấu hình ({$envKey}) trên máy chủ.");
    }

    /**
     * Xử lý Callback trả về từ Google / Facebook OAuth 2.0.
     */
    public function callback(Request $request, string $provider, SocialOAuthService $socialService): RedirectResponse
    {
        if (!$socialService->isSupported($provider)) {
            abort(404);
        }

        if ($request->filled('error')) {
            return redirect()->route('login')
                ->with('error', 'Bạn đã hủy ủy quyền đăng nhập qua ' . ($provider === 'google' ? 'Google' : 'Facebook') . '.');
        }

        $expectedState = (string) $request->session()->pull('social_oauth_state', '');
        $incomingState = (string) $request->input('state', '');

        if ($expectedState === '' || !hash_equals($expectedState, $incomingState)) {
            return redirect()->route('login')
                ->with('error', 'Phiên xác thực OAuth không hợp lệ hoặc đã hết hạn (sai tham số State). Vui lòng thử lại.');
        }

        try {
            // Hỗ trợ luồng giả lập OAuth nhanh trên môi trường Local/Testing khi chưa có Client ID
            if (!$socialService->isConfigured($provider) && app()->environment('local', 'testing') && $request->filled('dev_email')) {
                $devEmail = mb_strtolower(trim((string) $request->input('dev_email')));
                $devName = trim((string) $request->input('dev_name', explode('@', $devEmail)[0]));

                $socialData = [
                    'provider' => $provider,
                    'provider_id' => $provider . '_dev_' . substr(hash('sha256', $devEmail), 0, 16),
                    'email' => $devEmail,
                    'name' => $devName,
                    'avatar' => null,
                ];
            } else {
                $code = (string) $request->input('code', '');
                if ($code === '') {
                    return redirect()->route('login')
                        ->with('error', 'Không nhận được mã ủy quyền (Authorization Code) từ ' . ucfirst($provider) . '.');
                }

                $socialData = $socialService->fetchUserFromCode($provider, $code);
            }

            $user = $socialService->findOrCreateSocialUser($socialData);

            if (!$user->is_active) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Tài khoản của bạn đã bị khóa bởi Quản trị viên.']);
            }

            Auth::login($user, true);
            $request->session()->regenerate();

            $providerName = $provider === 'google' ? 'Google (Gmail)' : 'Facebook';
            AuditLog::record(
                "user.login_{$provider}",
                "Người dùng đăng nhập qua tài khoản {$providerName} ({$user->email}).",
                User::class,
                $user->id
            );

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', "Đăng nhập bằng {$providerName} thành công! Chào mừng Quản trị viên.");
            }

            return redirect()->intended(route('home'))
                ->with('success', "Đăng nhập bằng tài khoản {$providerName} thành công! Chúc bạn chơi game vui vẻ.");
        } catch (Throwable $e) {
            return redirect()->route('login')
                ->with('error', 'Lỗi xác thực OAuth: ' . $e->getMessage());
        }
    }
}

