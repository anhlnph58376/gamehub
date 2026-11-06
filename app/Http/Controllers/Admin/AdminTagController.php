<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminTagController extends Controller
{
    public function index(): View
    {
        $tags = Tag::withCount('games')->orderBy('name')->get();

        return view('admin.tags.index', compact('tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Vui lòng nhập tên nhãn (Tag).',
        ]);

        $slug = Str::slug($validated['name']);
        if (Tag::where('slug', $slug)->exists()) {
            $slug .= '-' . rand(10, 99);
        }

        $tag = Tag::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::record('tag.create', "Tạo nhãn '{$tag->name}'.", Tag::class, $tag->id);

        return back()->with('success', "Đã tạo nhãn '{$tag->name}' thành công.");
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $name = $tag->name;
        $tag->delete();

        AuditLog::record('tag.delete', "Xóa nhãn '{$name}'.", Tag::class);

        return back()->with('success', "Đã xóa nhãn '{$name}'.");
    }
}

