<?php

namespace App\Services\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SocialOAuthService
{
    public const SUPPORTED_PROVIDERS = ['google', 'facebook'];

    public function isSupported(string $provider): bool
    {
        return in_array($provider, self::SUPPORTED_PROVIDERS, true);
    }

    public function isConfigured(string $provider): bool
    {
        $clientId = trim((string) config("services.{$provider}.client_id", ''));
        $clientSecret = trim((string) config("services.{$provider}.client_secret", ''));

        return $clientId !== '' && $clientSecret !== '';
    }

    public function getRedirectUri(string $provider): string
    {
        $configured = trim((string) config("services.{$provider}.redirect", ''));
        if ($configured !== '') {
            return $configured;
        }

        return route('auth.social.callback', ['provider' => $provider]);
    }

    public function buildAuthorizationUrl(string $provider, string $state): string
    {
        $clientId = (string) config("services.{$provider}.client_id");
        $redirectUri = $this->getRedirectUri($provider);

        if ($provider === 'google') {
            return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => 'openid email profile',
                'state' => $state,
                'access_type' => 'online',
                'prompt' => 'select_account',
            ]);
        }

        if ($provider === 'facebook') {
            return 'https://www.facebook.com/v19.0/dialog/oauth?' . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => 'email,public_profile',
                'state' => $state,
            ]);
        }

        throw new RuntimeException("Nhà cung cấp OAuth không được hỗ trợ: {$provider}");
    }

    /**
     * Đổi Authorization Code lấy Access Token và truy xuất hồ sơ người dùng từ Google hoặc Facebook.
     */
    public function fetchUserFromCode(string $provider, string $code): array
    {
        $clientId = (string) config("services.{$provider}.client_id");
        $clientSecret = (string) config("services.{$provider}.client_secret");
        $redirectUri = $this->getRedirectUri($provider);

        if ($provider === 'google') {
            $tokenRes = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
                'grant_type' => 'authorization_code',
            ]);

            if (!$tokenRes->successful() || empty($tokenRes->json('access_token'))) {
                throw new RuntimeException('Không thể xác thực mã ủy quyền từ Google OAuth.');
            }

            $accessToken = $tokenRes->json('access_token');
            $profileRes = Http::withToken($accessToken)->timeout(15)->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if (!$profileRes->successful()) {
                throw new RuntimeException('Không thể lấy thông tin tài khoản từ Google.');
            }

            $profile = $profileRes->json();
            $email = mb_strtolower(trim((string) ($profile['email'] ?? '')));
            $providerId = (string) ($profile['sub'] ?? $profile['id'] ?? '');

            if ($email === '' || $providerId === '') {
                throw new RuntimeException('Tài khoản Google không cung cấp địa chỉ email hợp lệ.');
            }

            return [
                'provider' => 'google',
                'provider_id' => $providerId,
                'email' => $email,
                'name' => trim((string) ($profile['name'] ?? explode('@', $email)[0])),
                'avatar' => $profile['picture'] ?? null,
            ];
        }

        if ($provider === 'facebook') {
            $tokenRes = Http::timeout(15)->get('https://graph.facebook.com/v19.0/oauth/access_token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if (!$tokenRes->successful() || empty($tokenRes->json('access_token'))) {
                throw new RuntimeException('Không thể xác thực mã ủy quyền từ Facebook OAuth.');
            }

            $accessToken = $tokenRes->json('access_token');
            $profileRes = Http::timeout(15)->get('https://graph.facebook.com/v19.0/me', [
                'fields' => 'id,name,email,picture.type(large)',
                'access_token' => $accessToken,
            ]);

            if (!$profileRes->successful()) {
                throw new RuntimeException('Không thể lấy thông tin hồ sơ từ Facebook.');
            }

            $profile = $profileRes->json();
            $providerId = (string) ($profile['id'] ?? '');
            if ($providerId === '') {
                throw new RuntimeException('Không nhận diện được định danh tài khoản Facebook.');
            }

            $email = mb_strtolower(trim((string) ($profile['email'] ?? "fb_{$providerId}@facebook.gamehub.local")));

            return [
                'provider' => 'facebook',
                'provider_id' => $providerId,
                'email' => $email,
                'name' => trim((string) ($profile['name'] ?? 'Game Thủ Facebook')),
                'avatar' => $profile['picture']['data']['url'] ?? null,
            ];
        }

        throw new RuntimeException("Nhà cung cấp không hợp lệ: {$provider}");
    }

    /**
     * Đồng bộ hoặc khởi tạo tài khoản người dùng từ dữ liệu OAuth (Google / Facebook).
     */
    public function findOrCreateSocialUser(array $socialData): User
    {
        $provider = $socialData['provider'];
        $providerId = (string) $socialData['provider_id'];
        $email = mb_strtolower(trim((string) $socialData['email']));
        $name = trim((string) ($socialData['name'] ?: 'Game Thủ GameHub'));
        $avatar = $socialData['avatar'] ?? null;

        $idColumn = $provider === 'google' ? 'google_id' : 'facebook_id';

        // 1. Tìm theo google_id / facebook_id hoặc theo email đã đăng ký
        $user = User::where($idColumn, $providerId)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $updates = [
                $idColumn => $providerId,
                'oauth_provider' => $user->oauth_provider ?: $provider,
            ];

            if (!$user->email_verified_at) {
                $updates['email_verified_at'] = now();
            }

            if (empty($user->avatar) && !empty($avatar)) {
                $updates['avatar'] = $avatar;
            }

            $user->update($updates);

            return $user->fresh();
        }

        // 2. Tạo mới tài khoản người chơi và tặng 10 điểm khởi đầu
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            $idColumn => $providerId,
            'oauth_provider' => $provider,
            'password' => Hash::make(Str::random(32)),
            'avatar' => $avatar,
            'is_active' => true,
            'total_points' => 10,
        ]);

        $userRole = Role::firstOrCreate(
            ['slug' => 'user'],
            ['name' => 'Người chơi', 'description' => 'Tài khoản người chơi tiêu chuẩn']
        );

        $user->roles()->syncWithoutDetaching([$userRole->id]);

        return $user;
    }
}

