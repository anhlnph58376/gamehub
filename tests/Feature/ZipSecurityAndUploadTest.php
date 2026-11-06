<?php

namespace Tests\Feature;

use App\Services\GameUpload\CompatibilityCheckerService;
use App\Services\GameUpload\ZipValidatorService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class ZipSecurityAndUploadTest extends TestCase
{
    protected string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = storage_path('app/testing_zips_' . uniqid());
        File::makeDirectory($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (File::isDirectory($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }
        parent::tearDown();
    }

    public function test_valid_html5_game_zip_passes_validation(): void
    {
        $zipPath = $this->tempDir . '/valid_game.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('index.html', '<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width"></head><body><canvas id="game"></canvas></body></html>');
        $zip->addFromString('game.json', json_encode(['name' => 'Test Game', 'version' => '1.0.0']));
        $zip->close();

        $validator = new ZipValidatorService();
        $result = $validator->validate($zipPath);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
        $this->assertEquals(2, $result['stats']['file_count']);
    }

    public function test_fake_non_zip_file_is_rejected_by_magic_bytes(): void
    {
        $fakeZipPath = $this->tempDir . '/fake.zip';
        File::put($fakeZipPath, 'NOT_A_VALID_ZIP_HEADER_CONTENT');

        $validator = new ZipValidatorService();
        $result = $validator->validate($fakeZipPath);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_zip_slip_path_traversal_is_blocked(): void
    {
        $zipPath = $this->tempDir . '/zip_slip.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('index.html', '<html></html>');
        $zip->addFromString('../../traversal.txt', 'path traversal test');
        $zip->close();

        $validator = new ZipValidatorService();
        $result = $validator->validate($zipPath);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Path Traversal', implode(' ', $result['errors']));
    }

    public function test_forbidden_php_extension_inside_zip_is_blocked(): void
    {
        $zipPath = $this->tempDir . '/forbidden_ext.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('index.html', '<html></html>');
        $zip->addFromString('assets/script.php', '// forbidden server script extension test');
        $zip->close();

        $validator = new ZipValidatorService();
        $result = $validator->validate($zipPath);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('bị cấm', implode(' ', $result['errors']));
    }

    public function test_zip_without_index_html_is_rejected(): void
    {
        $zipPath = $this->tempDir . '/no_index.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('readme.txt', 'No index html here');
        $zip->close();

        $validator = new ZipValidatorService();
        $result = $validator->validate($zipPath);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('index.html', implode(' ', $result['errors']));
    }

    public function test_compatibility_checker_detects_missing_relative_assets(): void
    {
        $gameDir = $this->tempDir . '/extracted_game';
        File::makeDirectory($gameDir, 0755, true);
        File::put($gameDir . '/index.html', '<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width"><script src="missing.js"></script></head><body><canvas></canvas></body></html>');

        $checker = new CompatibilityCheckerService();
        $report = $checker->check($gameDir);

        $this->assertFalse($report['is_compatible']);
        $this->assertContains('missing.js', $report['missing_assets']);
    }
}

