<?php

namespace App\Services\Auth;

use App\Mail\AuthOtpMail;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailOtpService
{
    public const EXPIRES_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    /**
     * Khởi tạo mã xác thực 6 chữ số mới, lưu bản băm SHA-256 vào CSDL và gửi tới Email người dùng.
     */
    public function issueCode(User $user, string $purpose, ?string $ipAddress = null): string
    {
        $normalizedEmail = mb_strtolower(trim($user->email));

        // Vô hiệu hóa các mã cũ chưa sử dụng của cùng email và mục đích
        EmailVerificationCode::where('email', $normalizedEmail)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        $plainCode = (string) random_int(100000, 999999);

        EmailVerificationCode::create([
            'email' => $normalizedEmail,
            'purpose' => $purpose,
            'code_hash' => hash('sha256', $plainCode),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
            'ip_address' => $ipAddress,
        ]);

        Mail::to($user->email)->send(new AuthOtpMail(
            user: $user,
            code: $plainCode,
            purpose: $purpose,
            expiresInMinutes: self::EXPIRES_MINUTES,
            ipAddress: $ipAddress
        ));

        return $plainCode;
    }

    /**
     * Kiểm tra tính hợp lệ của mã OTP 6 số, giới hạn số lần thử sai (chống Brute-Force) và đánh dấu đã sử dụng.
     */
    public function verifyAndConsume(string $email, string $purpose, string $inputCode): array
    {
        $normalizedEmail = mb_strtolower(trim($email));
        $cleanCode = preg_replace('/\D/', '', $inputCode);

        $record = EmailVerificationCode::where('email', $normalizedEmail)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (!$record || $record->isExpired()) {
            return [
                'valid' => false,
                'message' => 'Mã xác thực không tồn tại hoặc đã hết hạn (quá ' . self::EXPIRES_MINUTES . ' phút). Vui lòng yêu cầu gửi lại mã mới.',
            ];
        }

        if ($record->attempts >= self::MAX_ATTEMPTS) {
            $record->update(['consumed_at' => now()]);

            return [
                'valid' => false,
                'message' => 'Bạn đã nhập sai mã quá ' . self::MAX_ATTEMPTS . ' lần. Mã này đã bị hủy vì lý do bảo mật, vui lòng yêu cầu mã mới.',
            ];
        }

        $inputHash = hash('sha256', $cleanCode);

        if (!hash_equals($record->code_hash, $inputHash)) {
            $record->increment('attempts');
            $remaining = max(0, self::MAX_ATTEMPTS - $record->attempts);

            if ($remaining === 0) {
                $record->update(['consumed_at' => now()]);

                return [
                    'valid' => false,
                    'message' => 'Mã xác thực không chính xác. Bạn đã hết lượt thử, vui lòng yêu cầu gửi lại mã mới.',
                ];
            }

            return [
                'valid' => false,
                'message' => "Mã xác thực 6 chữ số không chính xác (còn {$remaining} lần thử).",
            ];
        }

        $record->update(['consumed_at' => now()]);

        return [
            'valid' => true,
            'message' => 'Xác thực mã thành công.',
        ];
    }
}

