<?php

namespace App\Services\Audio;

use App\Models\AudioAsset;
use App\Models\AuditLog;
use App\Models\Game;
use App\Models\GameAudioAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AudioUploadService
{
    public const MAX_FILE_SIZE_BYTES = 15 * 1024 * 1024; // 15 MB

    public const ALLOWED_EXTENSIONS = ['mp3', 'ogg', 'wav'];

    public const FORBIDDEN_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'phar',
        'exe', 'sh', 'bash', 'bat', 'cmd', 'ps1', 'vbs', 'js', 'html', 'htm',
        'htaccess', 'env', 'py', 'pl', 'cgi', 'jsp', 'asp', 'aspx',
    ];

    /**
     * Kiểm tra tính hợp lệ và an toàn của tệp âm thanh tải lên (chống thực thi mã trên máy chủ).
     */
    public function validateUploadedAudio(UploadedFile $file): array
    {
        $originalName = $file->getClientOriginalName();
        $size = (int) $file->getSize();

        if ($size <= 0 || $size > self::MAX_FILE_SIZE_BYTES) {
            throw new InvalidArgumentException('Dung lượng tệp âm thanh không hợp lệ (tối đa 15MB).');
        }

        // Kiểm tra double extension & phần mở rộng nguy hiểm
        $parts = explode('.', strtolower($originalName));
        array_shift($parts);
        foreach ($parts as $part) {
            if (in_array($part, self::FORBIDDEN_EXTENSIONS, true)) {
                throw new InvalidArgumentException("Tệp chứa phần mở rộng bị cấm thực thi trên máy chủ (.{$part}).");
            }
        }

        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new InvalidArgumentException('Chỉ chấp nhận định dạng âm thanh chuẩn: MP3, OGG hoặc WAV.');
        }

        // Kiểm tra Magic Bytes đầu tệp
        $realPath = $file->getRealPath();
        $handle = $realPath ? @fopen($realPath, 'rb') : false;
        if (!$handle) {
            throw new InvalidArgumentException('Không thể đọc dữ liệu tệp âm thanh tải lên.');
        }
        $header = fread($handle, 12);
        fclose($handle);

        if (!$this->hasValidAudioSignature($header, $ext)) {
            throw new InvalidArgumentException('Chữ ký nhị phân (Magic Bytes) của tệp không khớp với định dạng âm thanh MP3/OGG/WAV.');
        }

        $mimeMap = [
            'wav' => 'audio/wav',
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
        ];

        return [
            'extension' => $ext,
            'mime_type' => $mimeMap[$ext] ?? 'audio/wav',
            'file_size' => $size,
        ];
    }

    public function hasValidAudioSignature(string $header, string $ext): bool
    {
        if (strlen($header) < 4) {
            return false;
        }

        if ($ext === 'wav') {
            return substr($header, 0, 4) === 'RIFF' && substr($header, 8, 4) === 'WAVE';
        }

        if ($ext === 'ogg') {
            return substr($header, 0, 4) === 'OggS';
        }

        if ($ext === 'mp3') {
            if (substr($header, 0, 3) === 'ID3') {
                return true;
            }
            $b0 = ord($header[0]);
            $b1 = ord($header[1]);

            return $b0 === 0xFF && (($b1 & 0xE0) === 0xE0);
        }

        return false;
    }

    /**
     * Lưu mới hoặc cập nhật AudioAsset từ Admin.
     */
    public function storeAudioAsset(array $data, UploadedFile $file, ?int $adminId = null): AudioAsset
    {
        $meta = $this->validateUploadedAudio($file);
        $slug = Str::slug($data['title']) . '-' . Str::lower(Str::random(5));
        $category = in_array($data['category'] ?? 'sfx', ['music', 'sfx', 'ambient'], true) ? $data['category'] : 'sfx';
        $scope = in_array($data['scope'] ?? 'both', ['website', 'game', 'both'], true) ? $data['scope'] : 'both';

        $fileName = "{$slug}.{$meta['extension']}";
        $relativePath = "audio/{$scope}/{$category}/{$fileName}";

        Storage::disk('public')->putFileAs("audio/{$scope}/{$category}", $file, $fileName);

        $asset = AudioAsset::create([
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'category' => $category,
            'scope' => $scope,
            'event_key' => $data['event_key'] ?: null,
            'file_path' => $relativePath,
            'mime_type' => $meta['mime_type'],
            'extension' => $meta['extension'],
            'file_size' => $meta['file_size'],
            'default_volume' => (float) ($data['default_volume'] ?? 0.80),
            'loop' => (bool) ($data['loop'] ?? ($category === 'music' || $category === 'ambient')),
            'source_type' => $data['source_type'] ?? 'project_generated',
            'license_name' => $data['license_name'] ?? 'CC0 1.0 Public Domain',
            'license_url' => $data['license_url'] ?? null,
            'usage_conditions' => $data['usage_conditions'] ?? null,
            'author_attribution' => $data['author_attribution'] ?? null,
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            'uploaded_by' => $adminId,
        ]);

        if (!empty($data['game_id']) && !empty($data['sound_key'])) {
            $game = Game::find($data['game_id']);
            if ($game) {
                $this->assignAudioToGame($asset, $game, $data['sound_key'], (float) ($data['default_volume'] ?? 0.80), (bool) $asset->loop);
            }
        }

        AuditLog::record(
            'audio.upload',
            "Tải lên tệp âm thanh mới: {$asset->title}",
            AudioAsset::class,
            $asset->id,
            ['title' => $asset->title, 'category' => $asset->category]
        );

        return $asset;
    }

    /**
     * Thay thế tệp âm thanh hoặc cập nhật thông tin của một AudioAsset.
     */
    public function updateAudioAsset(AudioAsset $asset, array $data, ?UploadedFile $newFile = null): AudioAsset
    {
        $oldValues = $asset->toArray();

        $updates = [
            'title' => $data['title'] ?? $asset->title,
            'description' => $data['description'] ?? $asset->description,
            'category' => $data['category'] ?? $asset->category,
            'scope' => $data['scope'] ?? $asset->scope,
            'event_key' => array_key_exists('event_key', $data) ? ($data['event_key'] ?: null) : $asset->event_key,
            'default_volume' => isset($data['default_volume']) ? (float) $data['default_volume'] : $asset->default_volume,
            'loop' => isset($data['loop']) ? (bool) $data['loop'] : $asset->loop,
            'source_type' => $data['source_type'] ?? $asset->source_type,
            'license_name' => $data['license_name'] ?? $asset->license_name,
            'license_url' => $data['license_url'] ?? $asset->license_url,
            'usage_conditions' => $data['usage_conditions'] ?? $asset->usage_conditions,
            'author_attribution' => $data['author_attribution'] ?? $asset->author_attribution,
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $asset->is_active,
        ];

        if ($newFile) {
            $meta = $this->validateUploadedAudio($newFile);
            if ($asset->file_path && Storage::disk('public')->exists($asset->file_path)) {
                Storage::disk('public')->delete($asset->file_path);
            }

            $fileName = "{$asset->slug}-" . Str::lower(Str::random(4)) . ".{$meta['extension']}";
            $folder = "audio/{$updates['scope']}/{$updates['category']}";
            Storage::disk('public')->putFileAs($folder, $newFile, $fileName);

            $updates['file_path'] = "{$folder}/{$fileName}";
            $updates['mime_type'] = $meta['mime_type'];
            $updates['extension'] = $meta['extension'];
            $updates['file_size'] = $meta['file_size'];
        }

        $asset->update($updates);

        AuditLog::record(
            'audio.update',
            "Cập nhật âm thanh: {$asset->title}",
            AudioAsset::class,
            $asset->id,
            ['old' => $oldValues['title'] ?? '', 'new' => $asset->title]
        );

        return $asset;
    }

    /**
     * Gắn âm thanh vào một Game cụ thể và đồng bộ vào cấu trúc thư mục assets/audio/{music,sfx,ambient}/ của game.
     */
    public function assignAudioToGame(
        AudioAsset $asset,
        Game $game,
        string $soundKey,
        float $volume = 0.80,
        bool $loop = false,
        bool $preload = true
    ): GameAudioAsset {
        $disk = Storage::disk('public');

        if ($disk->exists($asset->file_path) && $game->currentVersion && $game->currentVersion->storage_path) {
            $targetDir = trim($game->currentVersion->storage_path, '/') . "/assets/audio/{$asset->category}";
            $targetFile = "{$targetDir}/" . basename($asset->file_path);
            if ($asset->file_path !== $targetFile) {
                $disk->put($targetFile, $disk->get($asset->file_path));
            }
        }

        return GameAudioAsset::updateOrCreate(
            [
                'game_id' => $game->id,
                'sound_key' => trim($soundKey),
            ],
            [
                'audio_asset_id' => $asset->id,
                'audio_type' => $asset->category,
                'volume' => max(0.0, min(1.0, $volume)),
                'loop' => $loop,
                'preload' => $preload,
                'is_active' => true,
            ]
        );
    }

    public function deleteAudioAsset(AudioAsset $asset): void
    {
        $oldValues = $asset->toArray();
        if ($asset->file_path && Storage::disk('public')->exists($asset->file_path)) {
            Storage::disk('public')->delete($asset->file_path);
        }
        $asset->delete();

        AuditLog::record(
            'audio.delete',
            "Xóa âm thanh: {$oldValues['title']}",
            AudioAsset::class,
            $oldValues['id'] ?? null,
            ['deleted_title' => $oldValues['title']]
        );
    }
}
