<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AudioAsset;
use App\Models\Game;
use App\Models\GameAudioAsset;
use App\Services\Audio\AudioSynthesizerService;
use App\Services\Audio\AudioUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class AdminAudioController extends Controller
{
    public function __construct(
        private readonly AudioUploadService $audioUploadService,
        private readonly AudioSynthesizerService $audioSynthesizerService
    ) {}

    public function index(Request $request): View
    {
        $query = AudioAsset::with(['uploader', 'gameAssignments.game'])->latest();

        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('event_key', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($scope = $request->input('scope')) {
            $query->where('scope', $scope);
        }

        $audioAssets = $query->paginate(15)->withQueryString();
        $games = Game::orderBy('title')->get(['id', 'title', 'slug']);

        $stats = [
            'total_count' => AudioAsset::count(),
            'active_count' => AudioAsset::where('is_active', true)->count(),
            'music_count' => AudioAsset::where('category', 'music')->count(),
            'sfx_count' => AudioAsset::where('category', 'sfx')->count(),
            'ambient_count' => AudioAsset::where('category', 'ambient')->count(),
            'total_size_bytes' => (int) AudioAsset::sum('file_size'),
        ];

        return view('admin.audio.index', compact('audioAssets', 'games', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'in:music,sfx,ambient'],
            'scope' => ['required', 'in:website,game,both'],
            'event_key' => ['nullable', 'string', 'max:100'],
            'default_volume' => ['required', 'numeric', 'min:0', 'max:1'],
            'loop' => ['nullable', 'boolean'],
            'source_type' => ['required', 'in:project_generated,ai_generated,public_domain,licensed'],
            'license_name' => ['required', 'string', 'max:255'],
            'license_url' => ['nullable', 'url', 'max:255'],
            'usage_conditions' => ['nullable', 'string', 'max:1000'],
            'author_attribution' => ['nullable', 'string', 'max:180'],
            'audio_file' => ['required', 'file', 'max:15360'],
            'game_id' => ['nullable', 'exists:games,id'],
            'sound_key' => ['nullable', 'string', 'max:100'],
        ], [
            'title.required' => 'Vui lòng nhập tên tệp âm thanh.',
            'audio_file.required' => 'Vui lòng chọn tệp âm thanh (MP3, OGG hoặc WAV).',
            'audio_file.max' => 'Dung lượng tệp âm thanh không được vượt quá 15MB.',
            'license_name.required' => 'Vui lòng ghi rõ giấy phép bản quyền sử dụng âm thanh.',
        ]);

        try {
            $validated['loop'] = $request->boolean('loop');
            $validated['is_active'] = $request->boolean('is_active', true);

            $this->audioUploadService->storeAudioAsset(
                $validated,
                $request->file('audio_file'),
                $request->user()?->id
            );

            return back()->with('success', 'Đã tải lên và lưu tệp âm thanh thành công!');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, AudioAsset $audioAsset): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'in:music,sfx,ambient'],
            'scope' => ['required', 'in:website,game,both'],
            'event_key' => ['nullable', 'string', 'max:100'],
            'default_volume' => ['required', 'numeric', 'min:0', 'max:1'],
            'loop' => ['nullable', 'boolean'],
            'source_type' => ['required', 'in:project_generated,ai_generated,public_domain,licensed'],
            'license_name' => ['required', 'string', 'max:255'],
            'license_url' => ['nullable', 'url', 'max:255'],
            'usage_conditions' => ['nullable', 'string', 'max:1000'],
            'author_attribution' => ['nullable', 'string', 'max:180'],
            'audio_file' => ['nullable', 'file', 'max:15360'],
        ]);

        try {
            $validated['loop'] = $request->boolean('loop');
            $validated['is_active'] = $request->boolean('is_active', true);

            $this->audioUploadService->updateAudioAsset(
                $audioAsset,
                $validated,
                $request->file('audio_file')
            );

            return back()->with('success', "Đã cập nhật âm thanh \"{$audioAsset->title}\" thành công.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function toggleActive(AudioAsset $audioAsset): RedirectResponse
    {
        $audioAsset->update([
            'is_active' => !$audioAsset->is_active,
        ]);

        return back()->with(
            'success',
            $audioAsset->is_active
                ? "Đã kích hoạt âm thanh \"{$audioAsset->title}\"."
                : "Đã tạm tắt âm thanh \"{$audioAsset->title}\"."
        );
    }

    public function assignToGame(Request $request, AudioAsset $audioAsset): RedirectResponse
    {
        $validated = $request->validate([
            'game_id' => ['required', 'exists:games,id'],
            'sound_key' => ['required', 'string', 'max:100'],
            'volume' => ['required', 'numeric', 'min:0', 'max:1'],
            'loop' => ['nullable', 'boolean'],
            'preload' => ['nullable', 'boolean'],
        ]);

        $game = Game::findOrFail($validated['game_id']);
        $this->audioUploadService->assignAudioToGame(
            $audioAsset,
            $game,
            $validated['sound_key'],
            (float) $validated['volume'],
            $request->boolean('loop'),
            $request->boolean('preload', true)
        );

        return back()->with('success', "Đã gắn âm thanh \"{$audioAsset->title}\" vào game \"{$game->title}\" với mã khóa [{$validated['sound_key']}].");
    }

    public function removeGameAssignment(GameAudioAsset $assignment): RedirectResponse
    {
        $assignment->delete();

        return back()->with('success', 'Đã gỡ liên kết âm thanh khỏi trò chơi.');
    }

    public function destroy(AudioAsset $audioAsset): RedirectResponse
    {
        $title = $audioAsset->title;
        $this->audioUploadService->deleteAudioAsset($audioAsset);

        return back()->with('success', "Đã xóa vĩnh viễn tệp âm thanh \"{$title}\".");
    }

    public function synthesizeDefaults(Request $request): RedirectResponse
    {
        $this->audioSynthesizerService->provisionAllDefaultAudio($request->user()?->id);

        return back()->with('success', 'Đã tổng hợp và đồng bộ đầy đủ kho âm thanh chuẩn cho Website và 3 Game mẫu!');
    }
}

