<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuthOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
        public string $purpose = 'login_otp',
        public int $expiresInMinutes = 10,
        public ?string $ipAddress = null
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->purpose === 'password_reset'
            ? "[GameHub] Mã xác thực {$this->code} đặt lại mật khẩu của bạn"
            : "[GameHub] Mã đăng nhập {$this->code} vào tài khoản GameHub";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth-otp',
            with: [
                'user' => $this->user,
                'code' => $this->code,
                'purpose' => $this->purpose,
                'expiresInMinutes' => $this->expiresInMinutes,
                'ipAddress' => $this->ipAddress,
            ],
        );
    }
}

