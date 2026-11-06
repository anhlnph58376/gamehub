<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã Xác Thực GameHub</title>
</head>
<body style="margin:0; padding:0; background-color:#10121B; font-family:'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:#FFFFFF;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#10121B; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:520px; background-color:#181B28; border:1px solid #2A304A; border-radius:20px; overflow:hidden; box-shadow:0 20px 50px rgba(0,0,0,0.45);">
                    <!-- Header -->
                    <tr>
                        <td style="padding:28px 32px 20px 32px; text-align:center; background:linear-gradient(135deg, rgba(124,92,255,0.20), rgba(0,212,255,0.12)); border-bottom:1px solid #2A304A;">
                            <div style="display:inline-block; width:44px; height:44px; line-height:44px; border-radius:12px; background:linear-gradient(135deg, #7C5CFF, #00D4FF); color:#FFFFFF; font-size:22px; font-weight:900; text-align:center; margin-bottom:10px;">
                                G
                            </div>
                            <h1 style="margin:0; font-size:22px; font-weight:800; color:#FFFFFF;">
                                {{ $purpose === 'password_reset' ? 'Khôi Phục Mật Khẩu GameHub' : 'Mã Đăng Nhập GameHub' }}
                            </h1>
                            <p style="margin:6px 0 0 0; font-size:13px; color:#A5ABC0;">
                                Cổng Game HTML5 Trực Tuyến Chuẩn Quốc Tế
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:28px 32px;">
                            <p style="margin:0 0 14px 0; font-size:15px; color:#FFFFFF;">
                                Xin chào <strong>{{ $user->name }}</strong>,
                            </p>
                            <p style="margin:0 0 22px 0; font-size:14px; line-height:1.6; color:#A5ABC0;">
                                @if($purpose === 'password_reset')
                                    Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản GameHub gắn với địa chỉ email <strong>{{ $user->email }}</strong>. Vui lòng nhập mã xác thực 6 chữ số dưới đây để thiết lập mật khẩu mới:
                                @else
                                    Bạn vừa yêu cầu đăng nhập nhanh bằng email vào tài khoản GameHub (<strong>{{ $user->email }}</strong>). Vui lòng nhập mã xác thực 6 chữ số dưới đây để hoàn tất đăng nhập:
                                @endif
                            </p>

                            <!-- 6-digit OTP Box -->
                            <div style="margin:24px 0; padding:22px 16px; background-color:#202437; border:2px dashed #7C5CFF; border-radius:16px; text-align:center;">
                                <div style="font-size:11px; text-transform:uppercase; letter-spacing:2px; color:#00D4FF; font-weight:700; margin-bottom:8px;">
                                    MÃ XÁC THỰC 6 CHỮ SỐ CỦA BẠN
                                </div>
                                <div style="font-size:36px; font-weight:900; letter-spacing:10px; color:#FFFFFF; font-family:'Courier New', Courier, monospace;">
                                    {{ $code }}
                                </div>
                                <div style="margin-top:10px; font-size:12px; color:#FFB84D; font-weight:600;">
                                    ⏱ Mã có hiệu lực trong vòng {{ $expiresInMinutes }} phút
                                </div>
                            </div>

                            <!-- Security Notice -->
                            <div style="padding:14px 16px; background-color:rgba(255,184,77,0.08); border-left:3px solid #FFB84D; border-radius:8px; font-size:12px; line-height:1.5; color:#A5ABC0;">
                                <strong style="color:#FFB84D;">Lưu ý bảo mật:</strong> Tuyệt đối không chia sẻ mã xác thực này cho bất kỳ ai (kể cả Quản trị viên GameHub). Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này; tài khoản của bạn vẫn hoàn toàn an toàn.
                                @if($ipAddress)
                                    <br><span style="color:#6E7691;">Yêu cầu được gửi từ địa chỉ IP: {{ $ipAddress }} lúc {{ now()->format('H:i:s d/m/Y') }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:18px 32px; background-color:#131622; border-top:1px solid #2A304A; text-align:center; font-size:12px; color:#6E7691;">
                            © {{ date('Y') }} GameHub Portal. Email tự động từ hệ thống bảo mật tài khoản.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

