<?php

namespace App\Services\GameUpload;

use Illuminate\Support\Facades\File;

class CompatibilityCheckerService
{
    public function check(string $extractedAbsolutePath): array
    {
        $checks = [];
        $warnings = [];
        $errors = [];
        $missingAssets = [];

        $indexPath = $extractedAbsolutePath . DIRECTORY_SEPARATOR . 'index.html';
        $hasIndex = File::exists($indexPath) && File::size($indexPath) > 0;

        $checks['index_html'] = [
            'label' => 'Tệp khởi chạy index.html',
            'passed' => $hasIndex,
            'detail' => $hasIndex ? 'Hợp lệ' : 'Thiếu hoặc rỗng tệp index.html',
        ];

        if (!$hasIndex) {
            $errors[] = 'Không tìm thấy tệp index.html hợp lệ tại thư mục gốc của phiên bản game.';

            return [
                'is_compatible' => false,
                'score' => 0,
                'checks' => $checks,
                'warnings' => $warnings,
                'errors' => $errors,
                'missing_assets' => $missingAssets,
            ];
        }

        $htmlContent = File::get($indexPath);

        $hasViewport = (bool) preg_match('/<meta[^>]+name=["\']viewport["\']/i', $htmlContent);
        $checks['viewport_meta'] = [
            'label' => 'Thẻ Meta Viewport (Responsive Mobile)',
            'passed' => $hasViewport,
            'detail' => $hasViewport ? 'Đã cấu hình responsive viewport' : 'Chưa có thẻ meta viewport, có thể hiển thị nhỏ trên di động',
        ];

        if (!$hasViewport) {
            $warnings[] = 'Khuyến nghị thêm thẻ <meta name="viewport" content="width=device-width, initial-scale=1.0"> để tối ưu hóa trên điện thoại.';
        }

        $hasCanvasOrGameRoot = (bool) preg_match('/<(canvas|div[^>]+id=["\'](game|app|stage|phaser|unity|godot))/i', $htmlContent);
        $checks['render_surface'] = [
            'label' => 'Khung đồ họa HTML5 (Canvas / Game Container)',
            'passed' => $hasCanvasOrGameRoot,
            'detail' => $hasCanvasOrGameRoot ? 'Phát hiện thành phần hiển thị đồ họa HTML5' : 'Sử dụng DOM HTML tiêu chuẩn',
        ];

        $hasFrameBuster = (bool) preg_match('/(top\.location|parent\.location|window\.top\s*!==)/i', $htmlContent);
        $checks['iframe_sandbox_safe'] = [
            'label' => 'Tương thích nhúng Iframe Sandbox',
            'passed' => !$hasFrameBuster,
            'detail' => !$hasFrameBuster ? 'An toàn để nhúng vào Iframe Sandbox' : 'Phát hiện mã chuyển hướng top.location có thể xung đột với Sandbox',
        ];

        if ($hasFrameBuster) {
            $warnings[] = 'Phát hiện đoạn mã truy cập top.location/parent.location; Iframe Sandbox sẽ chặn hành vi chuyển hướng trang chính.';
        }

        preg_match_all('/(?:src|href)=["\']([^"\']+)["\']/i', $htmlContent, $matches);
        $referencedAssets = $matches[1] ?? [];

        foreach ($referencedAssets as $ref) {
            $ref = trim($ref);
            if (
                $ref === '' ||
                str_starts_with($ref, 'http://') ||
                str_starts_with($ref, 'https://') ||
                str_starts_with($ref, '//') ||
                str_starts_with($ref, 'data:') ||
                str_starts_with($ref, '#') ||
                str_starts_with($ref, 'javascript:')
            ) {
                continue;
            }

            $cleanRef = strtok($ref, '?#');
            $cleanRef = ltrim($cleanRef, './');
            $assetFullPath = $extractedAbsolutePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cleanRef);
            $publicRootPath = public_path(str_replace('/', DIRECTORY_SEPARATOR, $cleanRef));

            if (!File::exists($assetFullPath) && !(str_starts_with($ref, '/') && File::exists($publicRootPath))) {
                $missingAssets[] = $cleanRef;
            }
        }

        $assetsOk = empty($missingAssets);
        $checks['relative_assets'] = [
            'label' => 'Kiểm tra liên kết tài nguyên tĩnh (CSS/JS/Assets)',
            'passed' => $assetsOk,
            'detail' => $assetsOk
                ? 'Tất cả tài nguyên tham chiếu trong index.html đều tồn tại'
                : 'Thiếu ' . count($missingAssets) . ' tệp tài nguyên: ' . implode(', ', array_slice($missingAssets, 0, 5)),
        ];

        if (!$assetsOk) {
            $errors[] = 'Phát hiện tệp tài nguyên được khai báo trong index.html nhưng không tồn tại trong gói ZIP: ' . implode(', ', $missingAssets);
        }

        $hasManifest = File::exists($extractedAbsolutePath . DIRECTORY_SEPARATOR . 'game.json');
        $checks['game_manifest'] = [
            'label' => 'Tệp cấu hình game.json',
            'passed' => $hasManifest,
            'detail' => $hasManifest ? 'Đã tích hợp tệp metadata game.json' : 'Không có game.json (sử dụng cấu hình nhập từ biểu mẫu)',
        ];

        $passedCount = collect($checks)->where('passed', true)->count();
        $score = (int) round(($passedCount / max(count($checks), 1)) * 100);
        $isCompatible = empty($errors);

        return [
            'is_compatible' => $isCompatible,
            'score' => $score,
            'checks' => $checks,
            'warnings' => $warnings,
            'errors' => $errors,
            'missing_assets' => $missingAssets,
        ];
    }
}

