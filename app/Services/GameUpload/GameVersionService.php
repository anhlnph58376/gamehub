<?php

namespace App\Services\GameUpload;

use App\Models\AuditLog;
use App\Models\Game;
use App\Models\GameFile;
use App\Models\GameVersion;
use Illuminate\Support\Facades\DB;

class GameVersionService
{
    public function createVersion(
        Game $game,
        string $versionNumber,
        array $extractionData,
        array $validationStats,
        array $compatibilityReport,
        ?string $changelog = null,
        ?int $uploadedBy = null,
        bool $autoPublish = false
    ): GameVersion {
        return DB::transaction(function () use (
            $game,
            $versionNumber,
            $extractionData,
            $validationStats,
            $compatibilityReport,
            $changelog,
            $uploadedBy,
            $autoPublish
        ) {
            $version = GameVersion::create([
                'game_id' => $game->id,
                'version_number' => $versionNumber,
                'storage_path' => $extractionData['storage_path'],
                'entry_point' => $extractionData['entry_point'] ?? 'index.html',
                'file_size' => (int) ($validationStats['zip_size'] ?? 0),
                'extracted_size' => (int) ($validationStats['extracted_size'] ?? 0),
                'file_count' => (int) ($validationStats['file_count'] ?? count($extractionData['files'] ?? [])),
                'manifest_data' => $extractionData['manifest'] ?? null,
                'compatibility_report' => $compatibilityReport,
                'is_compatible' => (bool) ($compatibilityReport['is_compatible'] ?? true),
                'status' => $autoPublish ? 'published' : 'preview',
                'is_active' => false,
                'changelog' => $changelog ?? 'Phiên bản tải lên qua Game Upload Center.',
                'uploaded_by' => $uploadedBy ?? auth()->id(),
            ]);

            foreach ($extractionData['files'] ?? [] as $fileItem) {
                GameFile::create([
                    'game_version_id' => $version->id,
                    'file_path' => $fileItem['file_path'],
                    'type' => $fileItem['type'],
                    'mime_type' => $fileItem['mime_type'],
                    'size' => $fileItem['size'],
                ]);
            }

            if ($autoPublish && $version->is_compatible) {
                $this->publishVersion($version);
            }

            return $version->fresh(['files']);
        });
    }

    public function publishVersion(GameVersion $version): GameVersion
    {
        return DB::transaction(function () use ($version) {
            $game = $version->game;

            GameVersion::where('game_id', $game->id)
                ->where('id', '!=', $version->id)
                ->update([
                    'is_active' => false,
                    'status' => DB::raw("CASE WHEN status = 'published' THEN 'archived' ELSE status END"),
                ]);

            $version->update([
                'is_active' => true,
                'status' => 'published',
            ]);

            $game->update([
                'current_version_id' => $version->id,
                'status' => 'published',
                'published_at' => $game->published_at ?? now(),
            ]);

            AuditLog::record(
                'game_version.publish',
                "Xuất bản phiên bản v{$version->version_number} cho game '{$game->title}'.",
                GameVersion::class,
                $version->id
            );

            return $version->fresh();
        });
    }

    public function rollbackToVersion(GameVersion $targetVersion): GameVersion
    {
        return DB::transaction(function () use ($targetVersion) {
            $game = $targetVersion->game;

            GameVersion::where('game_id', $game->id)
                ->where('id', '!=', $targetVersion->id)
                ->update([
                    'is_active' => false,
                ]);

            $targetVersion->update([
                'is_active' => true,
                'status' => 'published',
            ]);

            $game->update([
                'current_version_id' => $targetVersion->id,
                'status' => 'published',
            ]);

            AuditLog::record(
                'game_version.rollback',
                "Khôi phục (Rollback) game '{$game->title}' về phiên bản v{$targetVersion->version_number}.",
                GameVersion::class,
                $targetVersion->id
            );

            return $targetVersion->fresh();
        });
    }
}

