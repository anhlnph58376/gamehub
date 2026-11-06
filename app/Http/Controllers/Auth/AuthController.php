<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        $user = User::where('email', $credentials['email'])->first();

        if ($user && !$user->is_active) {
            return back()->withErrors([
                'email' => 'Tài khoản của bạn đã bị khóa bởi Quản trị viên.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            AuditLog::record('user.login', 'Người dùng đăng nhập vào hệ thống.', User::class, Auth::id());

            if (Auth::user()->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Chào mừng Quản trị viên quay trở lại GameHub!');
            }

            return redirect()->intended(route('home'))
                ->with('success', 'Đăng nhập thành công! Chúc bạn chơi game vui vẻ.');
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'is_active' => true,
            'total_points' => 10,
            'email_verified_at' => now(),
        ]);

        $userRole = Role::firstOrCreate(
            ['slug' => 'user'],
            ['name' => 'Người chơi', 'description' => 'Tài khoản người chơi tiêu chuẩn']
        );

        $user->roles()->syncWithoutDetaching([$userRole->id]);

        Auth::login($user);
        $request->session()->regenerate();

        AuditLog::record('user.register', 'Người dùng đăng ký tài khoản mới.', User::class, $user->id);

        return redirect()->route('home')
            ->with('success', 'Đăng ký tài khoản thành công! Bạn được tặng 10 điểm khởi đầu.');
    }

    public function logout(Request $request): RedirectResponse
    {
        AuditLog::record('user.logout', 'Người dùng đăng xuất khỏi hệ thống.', User::class, Auth::id());

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Bạn đã đăng xuất thành công.');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email đã đăng ký.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
        ]);

        return back()->with('success', 'Nếu email tồn tại trên hệ thống, hướng dẫn đặt lại mật khẩu đã được gửi tới hộp thư của bạn.');
    }
}

