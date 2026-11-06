<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('roles')->withCount(['histories', 'reviews', 'favorites']);

        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function toggleLock(User $user): RedirectResponse
    {
        if ($user->id === auth()->id() || $user->isSuperAdmin()) {
            return back()->withErrors(['user' => 'Không thể khóa tài khoản Super Admin hoặc chính bạn.']);
        }

        $newState = !$user->is_active;
        $user->update([
            'is_active' => $newState,
            'locked_at' => $newState ? null : now(),
        ]);

        $actionText = $newState ? 'Mở khóa' : 'Khóa';
        AuditLog::record('user.toggle_lock', "{$actionText} tài khoản '{$user->email}'.", User::class, $user->id);

        return back()->with('success', "Đã {$actionText} tài khoản '{$user->name}' thành công.");
    }

    public function updateRoles(Request $request, User $user): RedirectResponse
    {
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return back()->withErrors(['user' => 'Chỉ Super Admin mới có thể thay đổi quyền của Super Admin.']);
        }

        $validated = $request->validate([
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ], [
            'role_ids.required' => 'Vui lòng chọn ít nhất một vai trò.',
        ]);

        $user->roles()->sync($validated['role_ids']);

        AuditLog::record('user.update_roles', "Cập nhật phân quyền cho tài khoản '{$user->email}'.", User::class, $user->id);

        return back()->with('success', "Đã cập nhật phân quyền cho '{$user->name}'.");
    }
}

