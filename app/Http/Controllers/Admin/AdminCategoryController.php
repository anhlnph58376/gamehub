<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('games')->orderBy('position')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:categories,slug'],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'slug.unique' => 'Đường dẫn tĩnh (slug) này đã tồn tại.',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        if (Category::where('slug', $slug)->exists()) {
            $slug .= '-' . rand(10, 99);
        }

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'icon' => $validated['icon'] ?: 'Gamepad2',
            'description' => $validated['description'] ?? null,
            'position' => (int) ($validated['position'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::record('category.create', "Tạo danh mục '{$category->name}'.", Category::class, $category->id);

        return back()->with('success', "Đã thêm danh mục '{$category->name}' thành công.");
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
        ]);

        $category->update([
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?: $category->icon,
            'description' => $validated['description'] ?? $category->description,
            'position' => (int) ($validated['position'] ?? $category->position),
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLog::record('category.update', "Cập nhật danh mục '{$category->name}'.", Category::class, $category->id);

        return back()->with('success', "Đã cập nhật danh mục '{$category->name}'.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->games()->exists()) {
            return back()->withErrors(['category' => 'Không thể xóa danh mục đang chứa trò chơi. Vui lòng chuyển trò chơi sang danh mục khác trước.']);
        }

        $name = $category->name;
        $category->delete();

        AuditLog::record('category.delete', "Xóa danh mục '{$name}'.", Category::class);

        return back()->with('success', "Đã xóa danh mục '{$name}'.");
    }
}

