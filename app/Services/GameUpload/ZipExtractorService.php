<?php

namespace App\Services\GameUpload;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class ZipExtractorService
{
    public function extract(string $zipFilePath, string $gameSlug, string $versionNumber): array
    {
        $cleanVersion = preg_replace('/[^a-zA-Z0-9._-]/', '', $versionNumber) ?: '1.0.0';
        $relativeStoragePath = "games/{$gameSlug}/v{$cleanVersion}";
        $targetAbsolutePath = Storage::disk('public')->path($relativeStoragePath);

        if (File::exists($targetAbsolutePath)) {
            File::deleteDirectory($targetAbsolutePath);
        }

        File::makeDirectory($targetAbsolutePath, 0755, true);

        $tempExtractDir = storage_path('app/temp_extract_' . uniqid($gameSlug . '_', true));
        File::makeDirectory($tempExtractDir, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath) !== true) {
            File::deleteDirectory($tempExtractDir);
            throw new RuntimeException('Không thể mở tệp ZIP để giải nén.');
        }

        $zip->extractTo($tempExtractDir);
        $zip->close();

        $sourceRoot = $this->resolveGameRootDirectory($tempExtractDir);

        File::copyDirectory($sourceRoot, $targetAbsolutePath);
        File::deleteDirectory($tempExtractDir);

        $manifest = $this->parseAndSanitizeManifest($targetAbsolutePath);
        $files = $this->collectExtractedFiles($targetAbsolutePath);

        return [
            'storage_path' => $relativeStoragePath,
            'absolute_path' => $targetAbsolutePath,
            'entry_point' => 'index.html',
            'manifest' => $manifest,
            'files' => $files,
        ];
    }

    protected function resolveGameRootDirectory(string $extractedDir): string
    {
        if (File::exists($extractedDir . DIRECTORY_SEPARATOR . 'index.html')) {
            return $extractedDir;
        }

        $directories = array_filter(
            File::directories($extractedDir),
            fn (string $dir) => !str_starts_with(basename($dir), '__MACOSX') && !str_starts_with(basename($dir), '.')
        );

        $filesInRoot = array_filter(
            File::files($extractedDir),
            fn ($file) => !str_starts_with($file->getFilename(), '.')
        );

        if (count($directories) === 1 && count($filesInRoot) === 0) {
            $subDir = reset($directories);
            if (File::exists($subDir . DIRECTORY_SEPARATOR . 'index.html')) {
                return $subDir;
            }
        }

        foreach ($directories as $dir) {
            if (File::exists($dir . DIRECTORY_SEPARATOR . 'index.html')) {
                return $dir;
            }
        }

        return $extractedDir;
    }

    protected function parseAndSanitizeManifest(string $rootPath): ?array
    {
        $manifestFile = $rootPath . DIRECTORY_SEPARATOR . 'game.json';
        if (!File::exists($manifestFile)) {
            return null;
        }

        $raw = File::get($manifestFile);
        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            return null;
        }

        $orientation = strtolower(strip_tags((string) ($decoded['orientation'] ?? 'landscape')));
        if (!in_array($orientation, ['landscape', 'portrait', 'any'], true)) {
            $orientation = 'landscape';
        }

        $controls = [];
        if (isset($decoded['controls']) && is_array($decoded['controls'])) {
            foreach ($decoded['controls'] as $key => $val) {
                if (is_scalar($val)) {
                    $controls[strip_tags((string) $key)] = strip_tags((string) $val);
                }
            }
        }

        return [
            'name' => isset($decoded['name']) ? trim(strip_tags((string) $decoded['name'])) : null,
            'description' => isset($decoded['description']) ? trim(strip_tags((string) $decoded['description'])) : null,
            'instructions' => isset($decoded['instructions']) ? trim(strip_tags((string) $decoded['instructions'])) : null,
            'version' => isset($decoded['version']) ? preg_replace('/[^0-9A-Za-z._-]/', '', (string) $decoded['version']) : '1.0.0',
            'author' => isset($decoded['author']) ? trim(strip_tags((string) $decoded['author'])) : 'GameHub Studio',
            'orientation' => $orientation,
            'controls' => $controls,
            'mobile_support' => isset($decoded['mobile_support']) ? (bool) $decoded['mobile_support'] : true,
            'thumbnail' => isset($decoded['thumbnail']) ? trim(strip_tags((string) $decoded['thumbnail'])) : null,
        ];
    }

    protected function collectExtractedFiles(string $rootPath): array
    {
        $allFiles = File::allFiles($rootPath);
        $result = [];

        foreach ($allFiles as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            $ext = strtolower($file->getExtension());

            $result[] = [
                'file_path' => $relativePath,
                'type' => $ext ?: 'unknown',
                'mime_type' => File::mimeType($file->getRealPath()) ?: 'application/octet-stream',
                'size' => $file->getSize(),
            ];
        }

        return $result;
    }
}

