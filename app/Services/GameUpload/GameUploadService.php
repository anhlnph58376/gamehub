<?php

namespace App\Services\GameUpload;

use App\Models\AuditLog;
use App\Models\Game;
use App\Models\GameVersion;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GameUploadService
{
    public function __construct(
        protected ZipValidatorService $validator,
        protected ZipExtractorService $extractor,
        protected CompatibilityCheckerService $compatibilityChecker,
        protected GameVersionService $versionService
    ) {
    }

    public function processUpload(
        Game $game,
        string $zipFilePath,
        ?string $versionNumber = null,
        ?string $changelog = null,
        bool $autoPublish = false,
        ?int $uploadedBy = null
    ): array {
        $validation = $this->validator->validate($zipFilePath);

        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors'],
                'version' => null,
                'compatibility' => null,
            ];
        }

        try {
            $tempVersion = $versionNumber ?: $this->suggestNextVersion($game);
            $extraction = $this->extractor->extract($zipFilePath, $game->slug, $tempVersion);

            $manifest = $extraction['manifest'];
            if (!$versionNumber && !empty($manifest['version'])) {
                $manifestVersion = $manifest['version'];
                if ($manifestVersion !== $tempVersion && !$game->versions()->where('version_number', $manifestVersion)->exists()) {
                    $newRelativePath = "games/{$game->slug}/v{$manifestVersion}";
                    $newAbsolutePath = Storage::disk('public')->path($newRelativePath);
                    if (!File::exists($newAbsolutePath)) {
                        File::moveDirectory($extraction['absolute_path'], $newAbsolutePath);
                        $extraction['storage_path'] = $newRelativePath;
                        $extraction['absolute_path'] = $newAbsolutePath;
                        $tempVersion = $manifestVersion;
                    }
                }
            }

            $compatibility = $this->compatibilityChecker->check($extraction['absolute_path']);

            $this->applyManifestMetadata($game, $manifest, $extraction['storage_path'], $extraction['absolute_path']);

            $version = $this->versionService->createVersion(
                game: $game,
                versionNumber: $tempVersion,
                extractionData: $extraction,
                validationStats: $validation['stats'],
                compatibilityReport: $compatibility,
                changelog: $changelog,
                uploadedBy: $uploadedBy,
                autoPublish: $autoPublish && $compatibility['is_compatible']
            );

            AuditLog::record(
                'game.upload_zip',
                "Tải lên gói game ZIP phiên bản v{$version->version_number} cho '{$game->title}'.",
                Game::class,
                $game->id,
                [
                    'version_id' => $version->id,
                    'version_number' => $version->version_number,
                    'is_compatible' => $compatibility['is_compatible'],
                ]
            );

            return [
                'success' => true,
                'errors' => [],
                'version' => $version,
                'compatibility' => $compatibility,
                'manifest' => $manifest,
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'errors' => [$e->getMessage()],
                'version' => null,
                'compatibility' => null,
            ];
        }
    }

    protected function suggestNextVersion(Game $game): string
    {
        $latest = GameVersion::where('game_id', $game->id)->latest('id')->first();
        if (!$latest) {
            return '1.0.0';
        }

        if (preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $latest->version_number, $m)) {
            return $m[1] . '.' . $m[2] . '.' . ((int) $m[3] + 1);
        }

        return '1.0.' . ($game->versions()->count() + 1);
    }

    protected function applyManifestMetadata(Game $game, ?array $manifest, string $storagePath, string $absolutePath): void
    {
        if (!$manifest) {
            return;
        }

        $updates = [];

        if (empty($game->description) && !empty($manifest['description'])) {
            $updates['description'] = $manifest['description'];
        }

        if (empty($game->short_description) && !empty($manifest['description'])) {
            $updates['short_description'] = mb_substr($manifest['description'], 0, 250);
        }

        if (empty($game->instructions) && !empty($manifest['instructions'])) {
            $updates['instructions'] = $manifest['instructions'];
        }

        if (empty($game->controls) && !empty($manifest['controls'])) {
            $updates['controls'] = $manifest['controls'];
        }

        if (!empty($manifest['author'])) {
            $updates['author'] = $manifest['author'];
        }

        if (!empty($manifest['orientation'])) {
            $updates['orientation'] = $manifest['orientation'];
        }

        if (isset($manifest['mobile_support'])) {
            $updates['mobile_support'] = (bool) $manifest['mobile_support'];
        }

        if (empty($game->thumbnail_path) && !empty($manifest['thumbnail'])) {
            $thumbRel = ltrim($manifest['thumbnail'], './');
            if (File::exists($absolutePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $thumbRel))) {
                $updates['thumbnail_path'] = $storagePath . '/' . $thumbRel;
            }
        }

        if (!empty($updates)) {
            $game->update($updates);
        }
    }
}

