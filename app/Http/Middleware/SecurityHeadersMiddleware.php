<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ẩn thông tin phiên bản PHP/Server để chống rò rỉ thông tin (Fingerprinting)
        $response->headers->remove('X-Powered-By');

        // Các tiêu đề bảo mật chuẩn OWASP
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');

        // Bật HSTS khi chạy trên HTTPS hoặc môi trường Production
        if ($request->isSecure() || app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Bảo vệ chống Clickjacking cho trang chính, chỉ cho phép nhúng Iframe từ cùng Origin
        if (!$request->is('storage/games/*') && !$request->is('games/*/frame*')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('Content-Security-Policy', "frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none'");
        }

        return $response;
    }
}
