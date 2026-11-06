<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Game;
use App\Models\GameVersion;
use App\Models\Tag;
use App\Services\GameManagement\GameManagementService;
use App\Services\GameUpload\GameUploadService;
use App\Services\GameUpload\GameVersionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminGameController extends Controller
{
    public function __construct(
        protected GameManagementService $gameService,
        protected GameUploadService $uploadService,
        protected GameVersionService $versionService
    ) {
    }

    public function index(Request $request): View
    {
        $query = Game::withTrashed()->with(['category', 'currentVersion', 'uploader']);

        if ($search = trim((string) $request->input('q', ''))) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            if ($status === 'trashed') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $status);
            }
        }

        if ($categoryId = $request->integer('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $games = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('position')->get();

        return view('admin.games.index', compact('games', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('position')->get();
        $tags = Tag::orderBy('name')->get();

        return view('admin.games.create', compact('categories', 'tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200'],
            'category_id' => ['required', 'exists:categories,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'controls_keyboard' => ['nullable', 'string', 'max:255'],
            'controls_mouse' => ['nullable', 'string', 'max:255'],
            'controls_touch' => ['nullable', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:150'],
            'orientation' => ['required', 'in:landscape,portrait,any'],
            'mobile_support' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_hot' => ['nullable', 'boolean'],
            'is_new' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draft,pending,published,hidden'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'game_zip' => ['nullable', 'file', 'max:51200'],
            'version_number' => ['nullable', 'string', 'max:50'],
            'changelog' => ['nullable', 'string', 'max:1000'],
            'auto_publish' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Vui lòng nhập tên trò chơi.',
            'category_id.required' => 'Vui lòng chọn danh mục trò chơi.',
            'category_id.exists' => 'Danh mục đã chọn không hợp lệ.',
        ]);

        $validated['mobile_support'] = $request->boolean('mobile_support');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_hot'] = $request->boolean('is_hot');
        $validated['is_new'] = $request->boolean('is_new');

        $game = $this->gameService->createGame($validated, $request->file('thumbnail'));

        if ($request->hasFile('game_zip')) {
            $zipFile = $request->file('game_zip');
            $uploadResult = $this->uploadService->processUpload(
                game: $game,
                zipFilePath: $zipFile->getRealPath(),
                versionNumber: $request->input('version_number') ?: '1.0.0',
                changelog: $request->input('changelog') ?: 'Phiên bản khởi tạo ban đầu.',
                autoPublish: $request->boolean('auto_publish', true),
                uploadedBy: auth()->id()
            );

            if (!$uploadResult['success']) {
                return redirect()->route('admin.games.edit', $game)
                    ->withErrors(['game_zip' => implode(' ', $uploadResult['errors'])])
                    ->with('warning', 'Đã tạo thông tin game nhưng gói ZIP không vượt qua kiểm tra bảo mật.');
            }
        }

        return redirect()->route('admin.games.edit', $game)
            ->with('success', "Đã tạo trò chơi '{$game->title}' thành công!");
    }

    public function edit(Game $game): View
    {
        $game->load(['category', 'tags', 'versions.uploader', 'versions.files', 'currentVersion']);
        $categories = Category::orderBy('position')->get();
        $tags = Tag::orderBy('name')->get();

        return view('admin.games.edit', compact('game', 'categories', 'tags'));
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'exists:categories,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'controls_keyboard' => ['nullable', 'string', 'max:255'],
            'controls_mouse' => ['nullable', 'string', 'max:255'],
            'controls_touch' => ['nullable', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:150'],
            'orientation' => ['required', 'in:landscape,portrait,any'],
            'status' => ['required', 'in:draft,pending,published,hidden,rejected'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
        ], [
            'title.required' => 'Vui lòng nhập tên trò chơi.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
        ]);

        $validated['mobile_support'] = $request->boolean('mobile_support');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_hot'] = $request->boolean('is_hot');
        $validated['is_new'] = $request->boolean('is_new');
        $validated['tag_ids'] = $request->input('tag_ids', []);

        $this->gameService->updateGame($game, $validated, $request->file('thumbnail'));

        return back()->with('success', 'Đã cập nhật thông tin trò chơi thành công.');
    }

    public function uploadVersion(Request $request, Game $game): RedirectResponse
    {
        $request->validate([
            'game_zip' => ['required', 'file', 'max:51200'],
            'version_number' => ['nullable', 'string', 'max:50'],
            'changelog' => ['nullable', 'string', 'max:1000'],
            'auto_publish' => ['nullable', 'boolean'],
        ], [
            'game_zip.required' => 'Vui lòng chọn tệp nén .zip của trò chơi.',
            'game_zip.max' => 'Dung lượng tệp ZIP không được vượt quá 50MB.',
        ]);

        $zipFile = $request->file('game_zip');
        $result = $this->uploadService->processUpload(
            game: $game,
            zipFilePath: $zipFile->getRealPath(),
            versionNumber: $request->input('version_number'),
            changelog: $request->input('changelog'),
            autoPublish: $request->boolean('auto_publish', false),
            uploadedBy: auth()->id()
        );

        if (!$result['success']) {
            return back()->withErrors(['game_zip' => implode(' | ', $result['errors'])]);
        }

        $version = $result['version'];
        $statusText = $version->is_active ? 'và đã Xuất bản ngay' : 'ở chế độ Preview (Chơi thử)';

        return back()->with('success', "Tải lên và giải nén phiên bản v{$version->version_number} thành công {$statusText}!");
    }

    public function publishVersion(Game $game, GameVersion $version): RedirectResponse
    {
        if ($version->game_id !== $game->id) {
            abort(404);
        }

        $this->versionService->publishVersion($version);

        return back()->with('success', "Đã xuất bản chính thức phiên bản v{$version->version_number} cho '{$game->title}'.");
    }

    public function rollbackVersion(Game $game, GameVersion $version): RedirectResponse
    {
        if ($version->game_id !== $game->id) {
            abort(404);
        }

        $this->versionService->rollbackToVersion($version);

        return back()->with('success', "Đã khôi phục (Rollback) '{$game->title}' về phiên bản v{$version->version_number} chỉ với 1 click!");
    }

    public function toggleFeatured(Game $game): RedirectResponse
    {
        $game->update(['is_featured' => !$game->is_featured]);

        return back()->with('success', 'Đã cập nhật trạng thái Nổi bật (Featured) cho trò chơi.');
    }

    public function destroy(Game $game): RedirectResponse
    {
        $title = $game->title;
        $id = $game->id;
        $game->delete();

        AuditLog::record('game.soft_delete', "Chuyển trò chơi '{$title}' vào thùng rác.", Game::class, $id);

        return back()->with('success', "Đã chuyển trò chơi '{$title}' vào thùng rác (Soft Delete).");
    }

    public function restore(int $id): RedirectResponse
    {
        $game = Game::onlyTrashed()->findOrFail($id);
        $game->restore();

        AuditLog::record('game.restore', "Khôi phục trò chơi '{$game->title}' từ thùng rác.", Game::class, $game->id);

        return back()->with('success', "Đã khôi phục trò chơi '{$game->title}' thành công.");
    }
}

