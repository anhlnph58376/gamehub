<?php

namespace App\Services\GameUpload;

use ZipArchive;

class ZipValidatorService
{
    public function validate(string $zipFilePath): array
    {
        $errors = [];

        if (!file_exists($zipFilePath) || !is_readable($zipFilePath)) {
            return [
                'valid' => false,
                'errors' => ['Không tìm thấy tệp ZIP hoặc tệp không thể đọc được.'],
                'stats' => [],
            ];
        }

        $fileSize = filesize($zipFilePath);
        $maxZipBytes = ((int) config('gamehub.max_zip_size_mb', 50)) * 1024 * 1024;

        if ($fileSize <= 0) {
            return [
                'valid' => false,
                'errors' => ['Tệp ZIP rỗng (0 byte).'],
                'stats' => [],
            ];
        }

        if ($fileSize > $maxZipBytes) {
            return [
                'valid' => false,
                'errors' => ['Dung lượng tệp ZIP vượt quá giới hạn cho phép (' . config('gamehub.max_zip_size_mb', 50) . 'MB).'],
                'stats' => [],
            ];
        }

        $handle = fopen($zipFilePath, 'rb');
        $magicBytes = $handle ? fread($handle, 4) : '';
        if ($handle) {
            fclose($handle);
        }

        if (!in_array($magicBytes, ["PK\x03\x04", "PK\x05\x06", "PK\x07\x08"], true)) {
            return [
                'valid' => false,
                'errors' => ['Tệp tải lên không phải định dạng ZIP hợp lệ (sai chữ ký Magic Bytes).'],
                'stats' => [],
            ];
        }

        $zip = new ZipArchive();
        $openResult = $zip->open($zipFilePath);

        if ($openResult !== true) {
            return [
                'valid' => false,
                'errors' => ['Không thể mở tệp ZIP (Mã lỗi: ' . $openResult . '). Tệp có thể bị hỏng.'],
                'stats' => [],
            ];
        }

        $numFiles = $zip->numFiles;
        $maxFileCount = (int) config('gamehub.max_file_count', 500);
        $maxExtractedBytes = ((int) config('gamehub.max_extracted_size_mb', 150)) * 1024 * 1024;
        $forbiddenExtensions = array_map('strtolower', config('gamehub.forbidden_extensions', [
            'php', 'phtml', 'phar', 'exe', 'sh', 'bat', 'cmd', 'htaccess', 'env',
        ]));

        if ($numFiles === 0) {
            $zip->close();
            return [
                'valid' => false,
                'errors' => ['Tệp ZIP không chứa bất kỳ tệp tin nào bên trong.'],
                'stats' => [],
            ];
        }

        if ($numFiles > $maxFileCount) {
            $zip->close();
            return [
                'valid' => false,
                'errors' => ["Số lượng tệp trong gói ZIP ({$numFiles}) vượt quá giới hạn bảo mật ({$maxFileCount} tệp) - Nghi vấn ZIP Bomb."],
                'stats' => [],
            ];
        }

        $totalUncompressedSize = 0;
        $hasIndexHtml = false;
        $validFileCount = 0;

        for ($i = 0; $i < $numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (!$stat) {
                continue;
            }

            $entryName = $stat['name'];
            $normalized = str_replace('\\', '/', $entryName);

            if (
                str_contains($normalized, '../') ||
                str_starts_with($normalized, '/') ||
                str_contains($normalized, '..\\') ||
                preg_match('/^[a-zA-Z]:/', $normalized)
            ) {
                $errors[] = "Phát hiện lỗ hổng Path Traversal (Zip Slip) tại đường dẫn: {$entryName}";
                break;
            }

            $totalUncompressedSize += (int) $stat['size'];
            if ($totalUncompressedSize > $maxExtractedBytes) {
                $errors[] = 'Tổng dung lượng sau giải nén vượt quá giới hạn cho phép (' . config('gamehub.max_extracted_size_mb', 150) . 'MB) - Nghi vấn ZIP Bomb.';
                break;
            }

            if (str_ends_with($normalized, '/')) {
                continue;
            }

            $validFileCount++;
            $basename = strtolower(basename($normalized));

            if ($basename === '.htaccess' || $basename === '.env' || $basename === '.user.ini') {
                $errors[] = "Phát hiện tệp cấu hình máy chủ bị cấm trong gói game: {$entryName}";
                break;
            }

            $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
            if (in_array($ext, $forbiddenExtensions, true) || preg_match('/\.(php\d*|phtml|phar)\./i', $basename)) {
                $errors[] = "Phát hiện tệp thực thi nguy hiểm bị cấm ({$ext}) tại: {$entryName}";
                break;
            }

            if ($basename === 'index.html') {
                $depth = substr_count(trim($normalized, '/'), '/');
                if ($depth <= 1) {
                    $hasIndexHtml = true;
                }
            }
        }

        $zip->close();

        if (empty($errors) && !$hasIndexHtml) {
            $errors[] = 'Gói game ZIP bắt buộc phải có tệp khởi chạy index.html ở thư mục gốc hoặc trong 1 thư mục con duy nhất.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'stats' => [
                'zip_size' => $fileSize,
                'extracted_size' => $totalUncompressedSize,
                'file_count' => $validFileCount,
            ],
        ];
    }
}

