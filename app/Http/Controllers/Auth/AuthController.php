<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\EmailOtpService;
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

            AuditLog::record('user.login', 'Người dùng đăng nhập bằng mật khẩu vào hệ thống.', User::class, Auth::id());

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

    /**
     * Gửi mã OTP 6 chữ số về Email để đăng nhập không cần mật khẩu (Passwordless Email OTP Login).
     */
    public function sendEmailLoginCode(Request $request, EmailOtpService $otpService): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email của bạn.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
        ]);

        $email = mb_strtolower(trim($validated['email']));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Không tìm thấy tài khoản nào được đăng ký với địa chỉ email này.',
            ])->withInput(['email' => $email, 'login_tab' => 'email_otp']);
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'email' => 'Tài khoản của bạn đã bị khóa bởi Quản trị viên.',
            ])->withInput(['email' => $email, 'login_tab' => 'email_otp']);
        }

        $otpService->issueCode($user, 'login_otp', $request->ip());

        AuditLog::record('user.login_otp_requested', "Yêu cầu gửi mã đăng nhập qua email cho {$user->email}.", User::class, $user->id);

        return redirect()->route('login.email.verify.form', ['email' => $email])
            ->with('success', "Mã đăng nhập 6 chữ số đã được gửi tới hộp thư {$email} (có hiệu lực trong 10 phút).");
    }

    /**
     * Hiển thị trang nhập mã OTP 6 số để hoàn tất đăng nhập bằng Email.
     */
    public function showVerifyEmailLogin(Request $request): View
    {
        $email = mb_strtolower(trim((string) $request->query('email', old('email', ''))));

        return view('auth.verify-email-login', compact('email'));
    }

    /**
     * Xác thực mã OTP 6 số và đăng nhập người dùng.
     */
    public function verifyEmailLogin(Request $request, EmailOtpService $otpService): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'code.required' => 'Vui lòng nhập mã xác thực gồm 6 chữ số.',
            'code.digits' => 'Mã xác thực phải bao gồm đúng 6 chữ số.',
        ]);

        $email = mb_strtolower(trim($validated['email']));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Tài khoản không tồn tại trên hệ thống.'])->onlyInput('email');
        }

        if (!$user->is_active) {
            return back()->withErrors(['email' => 'Tài khoản của bạn đã bị khóa bởi Quản trị viên.'])->onlyInput('email');
        }

        $verification = $otpService->verifyAndConsume($email, 'login_otp', $validated['code']);
        if (!$verification['valid']) {
            return back()->withErrors(['code' => $verification['message']])->onlyInput('email');
        }

        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        AuditLog::record('user.login_email_otp', 'Người dùng đăng nhập thành công bằng mã xác thực Email OTP.', User::class, $user->id);

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Đăng nhập bằng mã Email thành công! Chào mừng Quản trị viên.');
        }

        return redirect()->intended(route('home'))
            ->with('success', 'Đăng nhập bằng mã Email thành công! Chúc bạn chơi game vui vẻ.');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => mb_strtolower(trim($request->input('email'))),
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

    /**
     * Gửi mã xác thực 6 chữ số về Email của người dùng để khôi phục mật khẩu.
     */
    public function sendResetLink(Request $request, EmailOtpService $otpService): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email đã đăng ký.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
        ]);

        $email = mb_strtolower(trim($validated['email']));
        $user = User::where('email', $email)->first();

        if ($user && $user->is_active) {
            $otpService->issueCode($user, 'password_reset', $request->ip());

            AuditLog::record(
                'user.password_reset_requested',
                "Gửi mã OTP khôi phục mật khẩu tới email {$user->email}.",
                User::class,
                $user->id
            );
        }

        return redirect()->route('password.reset.form', ['email' => $email])
            ->with('success', "Nếu email tồn tại trên hệ thống, mã xác thực 6 chữ số đã được gửi tới hộp thư {$email} (hiệu lực 10 phút).");
    }

    /**
     * Hiển thị form nhập mã xác thực 6 chữ số từ Email và đặt mật khẩu mới.
     */
    public function showResetPassword(Request $request): View
    {
        $email = mb_strtolower(trim((string) $request->query('email', old('email', ''))));

        return view('auth.reset-password', compact('email'));
    }

    /**
     * Xác thực mã OTP 6 chữ số và cập nhật mật khẩu mới cho người dùng.
     */
    public function resetPassword(Request $request, EmailOtpService $otpService): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'code.required' => 'Vui lòng nhập mã xác thực 6 chữ số đã gửi tới email của bạn.',
            'code.digits' => 'Mã xác thực phải bao gồm đúng 6 chữ số.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có tối thiểu 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        $email = mb_strtolower(trim($validated['email']));
        $user = User::where('email', $email)->first();

        if (!$user || !$user->is_active) {
            return back()->withErrors([
                'email' => 'Không tìm thấy tài khoản hợp lệ ứng với địa chỉ email này.',
            ])->onlyInput('email');
        }

        $verification = $otpService->verifyAndConsume($email, 'password_reset', $validated['code']);
        if (!$verification['valid']) {
            return back()->withErrors([
                'code' => $verification['message'],
            ])->onlyInput('email');
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditLog::record(
            'user.password_reset_completed',
            'Người dùng đã đặt lại mật khẩu thành công qua mã xác thực Email.',
            User::class,
            $user->id
        );

        return redirect()->route('login')
            ->with('success', 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập bằng mật khẩu mới của bạn.');
    }
}
