<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để truy cập trang này.');
        }

        if (!$user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Quản trị viên.');
        }

        $user->loadMissing('roles');

        $allowedRoles = [];
        foreach ($roles as $roleGroup) {
            foreach (explode('|', $roleGroup) as $r) {
                $allowedRoles[] = trim($r);
            }
        }

        if (!$user->hasRole($allowedRoles)) {
            abort(403, 'Bạn không có quyền truy cập khu vực quản trị này.');
        }

        return $next($request);
    }
}

