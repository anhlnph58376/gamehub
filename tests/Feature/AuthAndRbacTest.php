<?php

namespace Tests\Feature;

use App\Mail\AuthOtpMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthAndRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_default_role_and_points(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Nguyễn Văn Game Thủ',
            'email' => 'nguyenvan@gamehub.local',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $user = User::where('email', 'nguyenvan@gamehub.local')->firstOrFail();
        $this->assertEquals(10, $user->total_points);
        $this->assertTrue($user->hasRole('user'));
    }

    public function test_locked_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'locked@gamehub.local',
            'password' => bcrypt('Password123!'),
            'is_active' => false,
        ]);

        $response = $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_standard_user_is_forbidden_from_admin_dashboard(): void
    {
        $userRole = Role::create(['name' => 'Người chơi', 'slug' => 'user']);
        $user = User::factory()->create();
        $user->roles()->attach($userRole->id);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_dashboard(): void
    {
        $adminRole = Role::create(['name' => 'Quản trị viên', 'slug' => 'admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->id);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Bảng Điều Khiển');
    }

    public function test_user_can_login_via_email_otp_code(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'otp-player@gamehub.local',
            'is_active' => true,
        ]);

        $sendRes = $this->post(route('login.email.send'), [
            'email' => $user->email,
        ]);

        $sendRes->assertRedirect(route('login.email.verify.form', ['email' => $user->email]));

        $sentCode = null;
        Mail::assertSent(AuthOtpMail::class, function (AuthOtpMail $mail) use ($user, &$sentCode) {
            $sentCode = $mail->code;

            return $mail->hasTo($user->email) && $mail->purpose === 'login_otp';
        });

        $this->assertNotNull($sentCode);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $sentCode);

        // Wrong code first
        $badRes = $this->post(route('login.email.verify.submit'), [
            'email' => $user->email,
            'code' => '000000',
        ]);
        $badRes->assertSessionHasErrors('code');
        $this->assertGuest();

        // Correct 6-digit code logs user in
        $verifyRes = $this->post(route('login.email.verify.submit'), [
            'email' => $user->email,
            'code' => $sentCode,
        ]);

        $verifyRes->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_reset_forgotten_password_via_email_otp_code(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'forgot@gamehub.local',
            'password' => Hash::make('OldPassword123!'),
            'is_active' => true,
        ]);

        $reqRes = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $reqRes->assertRedirect(route('password.reset.form', ['email' => $user->email]));

        $resetCode = null;
        Mail::assertSent(AuthOtpMail::class, function (AuthOtpMail $mail) use ($user, &$resetCode) {
            $resetCode = $mail->code;

            return $mail->hasTo($user->email) && $mail->purpose === 'password_reset';
        });

        $this->assertNotNull($resetCode);

        $resetRes = $this->post(route('password.reset.submit'), [
            'email' => $user->email,
            'code' => $resetCode,
            'password' => 'NewStrongPass2026!',
            'password_confirmation' => 'NewStrongPass2026!',
        ]);

        $resetRes->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('NewStrongPass2026!', $user->fresh()->password));

        // Reusing the same OTP code must be rejected
        $reuseRes = $this->post(route('password.reset.submit'), [
            'email' => $user->email,
            'code' => $resetCode,
            'password' => 'AnotherPass2026!',
            'password_confirmation' => 'AnotherPass2026!',
        ]);
        $reuseRes->assertSessionHasErrors('code');
    }

    public function test_user_can_login_with_google_gmail_and_facebook_oauth(): void
    {
        config([
            'services.google.client_id' => 'test-google-client-id',
            'services.google.client_secret' => 'test-google-secret',
            'services.facebook.client_id' => 'test-fb-client-id',
            'services.facebook.client_secret' => 'test-fb-secret',
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'mock-google-token'], 200),
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
                'sub' => 'google-uid-999888',
                'email' => 'gmailgamer@gmail.com',
                'name' => 'Gmail Pro Gamer',
                'picture' => 'https://lh3.googleusercontent.com/a/default-user',
            ], 200),
            'https://graph.facebook.com/v19.0/oauth/access_token*' => Http::response(['access_token' => 'mock-fb-token'], 200),
            'https://graph.facebook.com/v19.0/me*' => Http::response([
                'id' => 'fb-uid-777666',
                'email' => 'fbgamer@gmail.com',
                'name' => 'Facebook Gamer VN',
                'picture' => ['data' => ['url' => 'https://graph.facebook.com/fb-uid-777666/picture']],
            ], 200),
        ]);

        // 1. Google OAuth redirect & callback
        $googleRedirect = $this->get(route('auth.social.redirect', 'google'));
        $googleRedirect->assertRedirect();
        $this->assertStringContainsString('accounts.google.com/o/oauth2/v2/auth', $googleRedirect->headers->get('Location'));

        $state = session('social_oauth_state');
        $this->assertNotEmpty($state);

        $googleCallback = $this->get(route('auth.social.callback', [
            'provider' => 'google',
            'code' => 'valid-google-auth-code',
            'state' => $state,
        ]));

        $googleCallback->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $googleUser = User::where('email', 'gmailgamer@gmail.com')->firstOrFail();
        $this->assertEquals('google-uid-999888', $googleUser->google_id);
        $this->assertEquals('google', $googleUser->oauth_provider);
        $this->assertEquals(10, $googleUser->total_points);

        auth()->logout();

        // 2. Facebook OAuth redirect & callback
        $fbRedirect = $this->get(route('auth.social.redirect', 'facebook'));
        $fbRedirect->assertRedirect();
        $this->assertStringContainsString('facebook.com/v19.0/dialog/oauth', $fbRedirect->headers->get('Location'));

        $fbState = session('social_oauth_state');

        $fbCallback = $this->get(route('auth.social.callback', [
            'provider' => 'facebook',
            'code' => 'valid-fb-auth-code',
            'state' => $fbState,
        ]));

        $fbCallback->assertRedirect(route('home'));
        $fbUser = User::where('email', 'fbgamer@gmail.com')->firstOrFail();
        $this->assertEquals('fb-uid-777666', $fbUser->facebook_id);
        $this->assertEquals('facebook', $fbUser->oauth_provider);
    }
}

