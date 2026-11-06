<?php

namespace App\Services\GameManagement;

use App\Models\AuditLog;
use App\Models\Game;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GameManagementService
{
    public function createGame(array $data, ?UploadedFile $thumbnailFile = null): Game
    {
        return DB::transaction(function () use ($data, $thumbnailFile) {
            $slug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['title']);
            $originalSlug = $slug;
            $counter = 1;

            while (Game::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            $thumbnailPath = null;
            if ($thumbnailFile) {
                $thumbnailPath = $thumbnailFile->store('thumbnails', 'public');
            }

            $controls = $data['controls'] ?? [
                'keyboard' => $data['controls_keyboard'] ?? 'Phím mũi tên / WASD / Space',
                'mouse' => $data['controls_mouse'] ?? 'Click chuột để tương tác',
                'touch' => $data['controls_touch'] ?? 'Chạm trực tiếp vào màn hình cảm ứng',
            ];

            $game = Game::create([
                'title' => $data['title'],
                'slug' => $slug,
                'short_description' => $data['short_description'] ?? Str::limit(strip_tags($data['description'] ?? ''), 220),
                'description' => $data['description'] ?? null,
                'instructions' => $data['instructions'] ?? null,
                'controls' => $controls,
                'author' => $data['author'] ?? 'GameHub Studio',
                'thumbnail_path' => $thumbnailPath,
                'category_id' => (int) $data['category_id'],
                'status' => $data['status'] ?? 'draft',
                'orientation' => $data['orientation'] ?? 'landscape',
                'mobile_support' => (bool) ($data['mobile_support'] ?? true),
                'is_featured' => (bool) ($data['is_featured'] ?? false),
                'is_hot' => (bool) ($data['is_hot'] ?? false),
                'is_new' => (bool) ($data['is_new'] ?? true),
                'uploaded_by' => auth()->id(),
                'published_at' => ($data['status'] ?? 'draft') === 'published' ? now() : null,
            ]);

            if (!empty($data['tag_ids']) && is_array($data['tag_ids'])) {
                $game->tags()->sync($data['tag_ids']);
            }

            AuditLog::record('game.create', "Tạo mới trò chơi '{$game->title}'.", Game::class, $game->id);

            return $game;
        });
    }

    public function updateGame(Game $game, array $data, ?UploadedFile $thumbnailFile = null): Game
    {
        return DB::transaction(function () use ($game, $data, $thumbnailFile) {
            if ($thumbnailFile) {
                if ($game->thumbnail_path && Storage::disk('public')->exists($game->thumbnail_path)) {
                    Storage::disk('public')->delete($game->thumbnail_path);
                }
                $game->thumbnail_path = $thumbnailFile->store('thumbnails', 'public');
            }

            $controls = $game->controls ?? [];
            if (isset($data['controls_keyboard']) || isset($data['controls_mouse']) || isset($data['controls_touch'])) {
                $controls = [
                    'keyboard' => $data['controls_keyboard'] ?? ($controls['keyboard'] ?? ''),
                    'mouse' => $data['controls_mouse'] ?? ($controls['mouse'] ?? ''),
                    'touch' => $data['controls_touch'] ?? ($controls['touch'] ?? ''),
                ];
            }

            $game->update([
                'title' => $data['title'] ?? $game->title,
                'short_description' => $data['short_description'] ?? $game->short_description,
                'description' => $data['description'] ?? $game->description,
                'instructions' => $data['instructions'] ?? $game->instructions,
                'controls' => $controls,
                'author' => $data['author'] ?? $game->author,
                'thumbnail_path' => $game->thumbnail_path,
                'category_id' => isset($data['category_id']) ? (int) $data['category_id'] : $game->category_id,
                'status' => $data['status'] ?? $game->status,
                'orientation' => $data['orientation'] ?? $game->orientation,
                'mobile_support' => isset($data['mobile_support']) ? (bool) $data['mobile_support'] : $game->mobile_support,
                'is_featured' => isset($data['is_featured']) ? (bool) $data['is_featured'] : $game->is_featured,
                'is_hot' => isset($data['is_hot']) ? (bool) $data['is_hot'] : $game->is_hot,
                'is_new' => isset($data['is_new']) ? (bool) $data['is_new'] : $game->is_new,
                'published_at' => ($data['status'] ?? $game->status) === 'published' ? ($game->published_at ?? now()) : $game->published_at,
            ]);

            if (array_key_exists('tag_ids', $data)) {
                $game->tags()->sync(is_array($data['tag_ids']) ? $data['tag_ids'] : []);
            }

            AuditLog::record('game.update', "Cập nhật thông tin trò chơi '{$game->title}'.", Game::class, $game->id);

            return $game->fresh(['category', 'tags', 'currentVersion']);
        });
    }
}

