<?php

namespace App\Services\Audio;

use App\Models\AudioAsset;
use App\Models\Game;
use App\Models\GameAudioAsset;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AudioSynthesizerService
{
    private const SAMPLE_RATE = 22050;

    /**
     * Khởi tạo toàn bộ kho âm thanh chuẩn cho Website và các Game mẫu,
     * đồng thời lưu thông tin vào bảng audio_assets & game_audio_assets.
     */
    public function provisionAllDefaultAudio(?int $uploaderId = null): void
    {
        $disk = Storage::disk('public');

        // 1. Tạo các file âm thanh cho Website UI & BGM
        $websiteAssets = [
            [
                'title' => 'UI Button Click (Tactile Crisp)',
                'slug' => 'website-ui-click',
                'description' => 'Âm thanh click nút bấm giao diện ngắn gọn, sắc nét, cân bằng âm lượng.',
                'category' => 'sfx',
                'scope' => 'website',
                'event_key' => 'ui.click',
                'relative_path' => 'audio/website/sfx/ui-click.wav',
                'default_volume' => 0.65,
                'loop' => false,
                'duration_ms' => 75,
                'generator' => fn () => $this->synthesizeToneSequence([
                    ['freq' => 880.0, 'end_freq' => 1320.0, 'duration' => 0.045, 'amp' => 0.45],
                    ['freq' => 1760.0, 'end_freq' => 1320.0, 'duration' => 0.030, 'amp' => 0.30],
                ]),
            ],
            [
                'title' => 'UI Hover Tick (Soft Cyber)',
                'slug' => 'website-ui-hover',
                'description' => 'Âm thanh lướt chuột nhẹ nhàng trên thẻ game và nút điều hướng.',
                'category' => 'sfx',
                'scope' => 'website',
                'event_key' => 'ui.hover',
                'relative_path' => 'audio/website/sfx/ui-hover.wav',
                'default_volume' => 0.35,
                'loop' => false,
                'duration_ms' => 45,
                'generator' => fn () => $this->synthesizeToneSequence([
                    ['freq' => 1240.0, 'end_freq' => 1480.0, 'duration' => 0.045, 'amp' => 0.25],
                ]),
            ],
            [
                'title' => 'UI Notification Success Chime',
                'slug' => 'website-ui-success',
                'description' => 'Hợp âm báo thành công khi lưu đánh giá, yêu thích hoặc hoàn tất tác vụ.',
                'category' => 'sfx',
                'scope' => 'website',
                'event_key' => 'ui.success',
                'relative_path' => 'audio/website/sfx/ui-success.wav',
                'default_volume' => 0.75,
                'loop' => false,
                'duration_ms' => 360,
                'generator' => fn () => $this->synthesizeToneSequence([
                    ['freq' => 523.25, 'end_freq' => 523.25, 'duration' => 0.11, 'amp' => 0.45],
                    ['freq' => 659.25, 'end_freq' => 659.25, 'duration' => 0.11, 'amp' => 0.48],
                    ['freq' => 783.99, 'end_freq' => 1046.50, 'duration' => 0.16, 'amp' => 0.52],
                ]),
            ],
            [
                'title' => 'UI Notification Error Alert',
                'slug' => 'website-ui-error',
                'description' => 'Âm báo cảnh báo hoặc lỗi thao tác trên giao diện.',
                'category' => 'sfx',
                'scope' => 'website',
                'event_key' => 'ui.error',
                'relative_path' => 'audio/website/sfx/ui-error.wav',
                'default_volume' => 0.75,
                'loop' => false,
                'duration_ms' => 320,
                'generator' => fn () => $this->synthesizeToneSequence([
                    ['freq' => 349.23, 'end_freq' => 311.13, 'duration' => 0.14, 'amp' => 0.50],
                    ['freq' => 261.63, 'end_freq' => 220.00, 'duration' => 0.18, 'amp' => 0.50],
                ]),
            ],
            [
                'title' => 'UI Modal Open Whoosh',
                'slug' => 'website-ui-modal-open',
                'description' => 'Hiệu ứng mở hộp thoại cài đặt âm thanh, báo lỗi hoặc tìm kiếm nhanh.',
                'category' => 'sfx',
                'scope' => 'website',
                'event_key' => 'ui.modal_open',
                'relative_path' => 'audio/website/sfx/ui-modal-open.wav',
                'default_volume' => 0.60,
                'loop' => false,
                'duration_ms' => 160,
                'generator' => fn () => $this->synthesizeToneSequence([
                    ['freq' => 380.0, 'end_freq' => 760.0, 'duration' => 0.16, 'amp' => 0.38],
                ]),
            ],
            [
                'title' => 'UI Modal Close Whoosh',
                'slug' => 'website-ui-modal-close',
                'description' => 'Hiệu ứng đóng hộp thoại mượt mà.',
                'category' => 'sfx',
                'scope' => 'website',
                'event_key' => 'ui.modal_close',
                'relative_path' => 'audio/website/sfx/ui-modal-close.wav',
                'default_volume' => 0.55,
                'loop' => false,
                'duration_ms' => 150,
                'generator' => fn () => $this->synthesizeToneSequence([
                    ['freq' => 720.0, 'end_freq' => 340.0, 'duration' => 0.15, 'amp' => 0.35],
                ]),
            ],
            [
                'title' => 'Homepage Synthwave Ambient BGM',
                'slug' => 'website-bgm-home',
                'description' => 'Nhạc nền điện tử êm dịu dành cho trang chủ GameHub.',
                'category' => 'music',
                'scope' => 'website',
                'event_key' => 'bgm.home',
                'relative_path' => 'audio/website/music/bgm-home.wav',
                'default_volume' => 0.40,
                'loop' => true,
                'duration_ms' => 3200,
                'generator' => fn () => $this->synthesizeChordLoop([
                    [261.63, 329.63, 392.00], // C major
                    [220.00, 261.63, 329.63], // A minor
                    [174.61, 220.00, 261.63], // F major
                    [196.00, 246.94, 293.66], // G major
                ], 0.80, 0.32),
            ],
            [
                'title' => 'Explore Portal Chill Pad BGM',
                'slug' => 'website-bgm-explore',
                'description' => 'Nhạc nền thư giãn cho khu vực khám phá danh mục trò chơi.',
                'category' => 'music',
                'scope' => 'website',
                'event_key' => 'bgm.explore',
                'relative_path' => 'audio/website/music/bgm-explore.wav',
                'default_volume' => 0.38,
                'loop' => true,
                'duration_ms' => 3200,
                'generator' => fn () => $this->synthesizeChordLoop([
                    [293.66, 349.23, 440.00], // D minor
                    [233.08, 293.66, 349.23], // Bb major
                    [261.63, 329.63, 392.00], // C major
                    [220.00, 277.18, 329.63], // A major
                ], 0.80, 0.30),
            ],
        ];

        foreach ($websiteAssets as $item) {
            $wavBytes = ($item['generator'])();
            $disk->put($item['relative_path'], $wavBytes);

            AudioAsset::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'category' => $item['category'],
                    'scope' => $item['scope'],
                    'event_key' => $item['event_key'],
                    'file_path' => $item['relative_path'],
                    'mime_type' => 'audio/wav',
                    'extension' => 'wav',
                    'file_size' => strlen($wavBytes),
                    'duration_ms' => $item['duration_ms'],
                    'default_volume' => $item['default_volume'],
                    'loop' => $item['loop'],
                    'source_type' => 'project_generated',
                    'license_name' => 'CC0 1.0 Public Domain - GameHub DSP Synthesizer',
                    'license_url' => 'https://creativecommons.org/publicdomain/zero/1.0/',
                    'usage_conditions' => 'Được tổng hợp trực tiếp bởi dự án GameHub, miễn phí bản quyền 100% cho mục đích thương mại và phi thương mại.',
                    'author_attribution' => 'GameHub Audio Engine',
                    'is_active' => true,
                    'uploaded_by' => $uploaderId,
                ]
            );
        }

        // 2. Tạo cấu trúc assets/audio/{music,sfx,ambient} cho từng Game mẫu
        $gameAudioCatalog = [
            'pixel-runner' => [
                [
                    'sound_key' => 'bgm_main',
                    'title' => 'Pixel Runner Cyber Dash BGM',
                    'category' => 'music',
                    'subpath' => 'assets/audio/music/runner-bgm.wav',
                    'volume' => 0.45,
                    'loop' => true,
                    'duration_ms' => 2400,
                    'generator' => fn () => $this->synthesizeChordLoop([
                        [329.63, 392.00, 493.88],
                        [261.63, 329.63, 392.00],
                        [293.66, 369.99, 440.00],
                        [246.94, 329.63, 392.00],
                    ], 0.60, 0.35),
                ],
                [
                    'sound_key' => 'sfx_jump',
                    'title' => 'Pixel Runner Jump SFX',
                    'category' => 'sfx',
                    'subpath' => 'assets/audio/sfx/jump.wav',
                    'volume' => 0.80,
                    'loop' => false,
                    'duration_ms' => 140,
                    'generator' => fn () => $this->synthesizeToneSequence([
                        ['freq' => 280.0, 'end_freq' => 680.0, 'duration' => 0.14, 'amp' => 0.50],
                    ]),
                ],
                [
                    'sound_key' => 'sfx_score',
                    'title' => 'Pixel Runner Orb Collect SFX',
                    'category' => 'sfx',
                    'subpath' => 'assets/audio/sfx/score.wav',
                    'volume' => 0.85,
                    'loop' => false,
                    'duration_ms' => 180,
                    'generator' => fn () => $this->synthesizeToneSequence([
                        ['freq' => 587.33, 'end_freq' => 587.33, 'duration' => 0.08, 'amp' => 0.48],
                        ['freq' => 880.00, 'end_freq' => 1174.66, 'duration' => 0.10, 'amp' => 0.52],
                    ]),
                ],
                [
                    'sound_key' => 'sfx_gameover',
                    'title' => 'Pixel Runner Crash Game Over SFX',
                    'category' => 'sfx',
                    'subpath' => 'assets/audio/sfx/gameover.wav',
                    'volume' => 0.85,
                    'loop' => false,
                    'duration_ms' => 380,
                    'generator' => fn () => $this->synthesizeToneSequence([
                        ['freq' => 392.00, 'end_freq' => 293.66, 'duration' => 0.18, 'amp' => 0.50],
                        ['freq' => 261.63, 'end_freq' => 130.81, 'duration' => 0.20, 'amp' => 0.52],
                    ]),
                ],
                [
                    'sound_key' => 'ambient_wind',
                    'title' => 'Pixel Runner Cyber City Wind Ambient',
                    'category' => 'ambient',
                    'subpath' => 'assets/audio/ambient/cyber-wind.wav',
                    'volume' => 0.25,
                    'loop' => true,
                    'duration_ms' => 1600,
                    'generator' => fn () => $this->synthesizeChordLoop([
                        [110.00, 164.81, 220.00],
                        [123.47, 185.00, 246.94],
                    ], 0.80, 0.20),
                ],
            ],
            'space-shooter' => [
                [
                    'sound_key' => 'bgm_main',
                    'title' => 'Space Shooter Galactic Battle BGM',
                    'category' => 'music',
                    'subpath' => 'assets/audio/music/space-bgm.wav',
                    'volume' => 0.45,
                    'loop' => true,
                    'duration_ms' => 2400,
                    'generator' => fn () => $this->synthesizeChordLoop([
                        [220.00, 261.63, 329.63],
                        [196.00, 246.94, 293.66],
                        [174.61, 220.00, 261.63],
                        [164.81, 207.65, 246.94],
                    ], 0.60, 0.34),
                ],
                [
                    'sound_key' => 'sfx_laser',
                    'title' => 'Space Shooter Plasma Laser SFX',
                    'category' => 'sfx',
                    'subpath' => 'assets/audio/sfx/laser.wav',
                    'volume' => 0.75,
                    'loop' => false,
                    'duration_ms' => 95,
                    'generator' => fn () => $this->synthesizeToneSequence([
                        ['freq' => 1100.0, 'end_freq' => 240.0, 'duration' => 0.095, 'amp' => 0.46],
                    ]),
                ],
                [
                    'sound_key' => 'sfx_explosion',
                    'title' => 'Space Shooter Enemy Explosion SFX',
                    'category' => 'sfx',
                    'subpath' => 'assets/audio/sfx/explosion.wav',
                    'volume' => 0.80,
                    'loop' => false,
                    'duration_ms' => 220,
                    'generator' => fn () => $this->synthesizeToneSequence([
                        ['freq' => 180.0, 'end_freq' => 65.0, 'duration' => 0.22, 'amp' => 0.55],
                    ]),
                ],
                [
                    'sound_key' => 'sfx_powerup',
                    'title' => 'Space Shooter Weapon Upgrade SFX',
                    'category' => 'sfx',
                    'subpath' => 'assets/audio/sfx/powerup.wav',
                    'volume' => 0.85,
                    'loop' => false,
                    'duration_ms' => 240,
                    'generator' => fn () => $this->synthesizeToneSequence([
                        ['freq' => 440.0, 'end_freq' => 659.25, 'duration' => 0.12, 'amp' => 0.45],
                        ['freq' => 659.25, 'end_freq' => 1046.50, 'duration' => 0.12, 'amp' => 0.50],
                    ]),
                ],
                [
                    'sound_key' => 'ambient_space',
                    'title' => 'Space Shooter Deep Nebula Hum Ambient',
                    'category' => 'ambient',
                    'subpath' => 'assets/audio/ambient/deep-space.wav',
                    'volume' => 0.25,
                    'loop' => true,
                    'duration_ms' => 1600,
                    'generator' => fn () => $this->synthesizeChordLoop([
                        [98.00, 146.83, 196.00],
                        [110.00, 164.81, 220.00],
                    ], 0.80, 0.22),
                ],
            ],
            'puzzle-blocks' => [
                [
                    'sound_key' => 'bgm_main',
                    'title' => 'Puzzle Blocks Crystal Zen BGM',
                    'category' => 'music',
                    'subpath' => 'assets/audio/music/puzzle-bgm.wav',
                    'volume' => 0.42,
                    'loop' => true,
                    'duration_ms' => 2400,
                    'generator' => fn () => $this->synthesizeChordLoop([
                        [261.63, 329.63, 493.88],
                        [220.00, 293.66, 440.00],
                        [196.00, 246.94, 392.00],
                        [261.63, 329.63, 392.00],
                    ], 0.60, 0.30),
                ],
                [
                    'sound_key' => 'sfx_place',
                    'title' => 'Puzzle Blocks Snap Block SFX',
                    'category' => 'sfx',
                    'subpath' => 'assets/audio/sfx/place.wav',
                    'volume' => 0.80,
                    'loop' => false,
                    'duration_ms' => 85,
                    'generator' => fn () => $this->synthesizeToneSequence([
                        ['freq' => 360.0, 'end_freq' => 540.0, 'duration' => 0.085, 'amp' => 0.48],
                    ]),
                ],
                [
                    'sound_key' => 'sfx_clear',
                    'title' => 'Puzzle Blocks Line Combo Clear SFX',
                    'category' => 'sfx',
                    'subpath' => 'assets/audio/sfx/clear-line.wav',
                    'volume' => 0.88,
                    'loop' => false,
                    'duration_ms' => 300,
                    'generator' => fn () => $this->synthesizeToneSequence([
                        ['freq' => 523.25, 'end_freq' => 659.25, 'duration' => 0.10, 'amp' => 0.45],
                        ['freq' => 659.25, 'end_freq' => 783.99, 'duration' => 0.10, 'amp' => 0.48],
                        ['freq' => 1046.50, 'end_freq' => 1318.51, 'duration' => 0.10, 'amp' => 0.52],
                    ]),
                ],
                [
                    'sound_key' => 'sfx_gameover',
                    'title' => 'Puzzle Blocks No Moves Game Over SFX',
                    'category' => 'sfx',
                    'subpath' => 'assets/audio/sfx/gameover.wav',
                    'volume' => 0.80,
                    'loop' => false,
                    'duration_ms' => 340,
                    'generator' => fn () => $this->synthesizeToneSequence([
                        ['freq' => 329.63, 'end_freq' => 246.94, 'duration' => 0.16, 'amp' => 0.46],
                        ['freq' => 220.00, 'end_freq' => 164.81, 'duration' => 0.18, 'amp' => 0.48],
                    ]),
                ],
                [
                    'sound_key' => 'ambient_zen',
                    'title' => 'Puzzle Blocks Soft Crystal Ambient',
                    'category' => 'ambient',
                    'subpath' => 'assets/audio/ambient/zen-hum.wav',
                    'volume' => 0.22,
                    'loop' => true,
                    'duration_ms' => 1600,
                    'generator' => fn () => $this->synthesizeChordLoop([
                        [130.81, 196.00, 261.63],
                        [146.83, 220.00, 293.66],
                    ], 0.80, 0.20),
                ],
            ],
        ];

        foreach ($gameAudioCatalog as $gameSlug => $soundItems) {
            $game = Game::where('slug', $gameSlug)->with('currentVersion')->first();

            foreach ($soundItems as $item) {
                $wavBytes = ($item['generator'])();

                // 1) Lưu vào thư mục sample-games/{slug}/assets/audio/{music,sfx,ambient}/
                $sampleSourcePath = base_path("games/sample-games/{$gameSlug}/{$item['subpath']}");
                File::ensureDirectoryExists(dirname($sampleSourcePath));
                File::put($sampleSourcePath, $wavBytes);

                // 2) Lưu vào thư mục public disk games/{slug}/assets/audio/... và version hiện tại
                $publicStoragePath = "games/{$gameSlug}/{$item['subpath']}";
                $disk->put($publicStoragePath, $wavBytes);

                if ($game && $game->currentVersion && $game->currentVersion->storage_path) {
                    $versionFilePath = trim($game->currentVersion->storage_path, '/') . '/' . $item['subpath'];
                    $disk->put($versionFilePath, $wavBytes);
                    $publicStoragePath = $versionFilePath;
                }

                $assetSlug = Str::slug("{$gameSlug}-{$item['sound_key']}");
                $audioAsset = AudioAsset::updateOrCreate(
                    ['slug' => $assetSlug],
                    [
                        'title' => $item['title'],
                        'description' => "Tài nguyên âm thanh ({$item['category']}) của tựa game {$gameSlug}.",
                        'category' => $item['category'],
                        'scope' => 'game',
                        'event_key' => "game.{$gameSlug}.{$item['sound_key']}",
                        'file_path' => $publicStoragePath,
                        'mime_type' => 'audio/wav',
                        'extension' => 'wav',
                        'file_size' => strlen($wavBytes),
                        'duration_ms' => $item['duration_ms'],
                        'default_volume' => $item['volume'],
                        'loop' => $item['loop'],
                        'source_type' => 'project_generated',
                        'license_name' => 'CC0 1.0 Public Domain - GameHub DSP Synthesizer',
                        'license_url' => 'https://creativecommons.org/publicdomain/zero/1.0/',
                        'usage_conditions' => 'Được tổng hợp trực tiếp bởi dự án GameHub, miễn phí bản quyền 100%.',
                        'author_attribution' => 'GameHub Audio Engine',
                        'is_active' => true,
                        'uploaded_by' => $uploaderId,
                    ]
                );

                if ($game) {
                    GameAudioAsset::updateOrCreate(
                        [
                            'game_id' => $game->id,
                            'sound_key' => $item['sound_key'],
                        ],
                        [
                            'audio_asset_id' => $audioAsset->id,
                            'audio_type' => $item['category'],
                            'volume' => $item['volume'],
                            'loop' => $item['loop'],
                            'preload' => true,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Tổng hợp chuỗi âm thanh đơn âm/đa âm có đường bao ADSR mượt mà (không bị tiếng bụp/rè).
     */
    public function synthesizeToneSequence(array $segments): string
    {
        $pcm = '';

        foreach ($segments as $seg) {
            $freqStart = (float) ($seg['freq'] ?? 440.0);
            $freqEnd = (float) ($seg['end_freq'] ?? $freqStart);
            $duration = max(0.01, (float) ($seg['duration'] ?? 0.1));
            $amp = min(0.85, max(0.05, (float) ($seg['amp'] ?? 0.4)));

            $numSamples = (int) round($duration * self::SAMPLE_RATE);
            $attackSamples = max(1, (int) round($numSamples * 0.12));
            $releaseSamples = max(1, (int) round($numSamples * 0.28));

            $phase = 0.0;
            for ($i = 0; $i < $numSamples; $i++) {
                $progress = $i / max(1, $numSamples - 1);
                $freq = $freqStart + ($freqEnd - $freqStart) * $progress;
                $phase += (2.0 * M_PI * $freq) / self::SAMPLE_RATE;

                // Envelope ADSR chống click/clipping
                $env = 1.0;
                if ($i < $attackSamples) {
                    $env = $i / $attackSamples;
                } elseif ($i > $numSamples - $releaseSamples) {
                    $env = max(0.0, ($numSamples - $i) / $releaseSamples);
                }

                // Kết hợp sóng Sine cơ bản và hài bậc 2 nhẹ để âm thanh ấm và trong
                $val = (0.82 * sin($phase) + 0.18 * sin($phase * 2.0)) * $amp * $env;
                $sampleInt = (int) max(-32767, min(32767, round($val * 32767)));
                $pcm .= pack('v', $sampleInt & 0xFFFF);
            }
        }

        return $this->wrapWavHeader($pcm, self::SAMPLE_RATE, 1, 16);
    }

    /**
     * Tổng hợp vòng lặp hợp âm (Chord Progression Loop) êm dịu cho BGM & Ambient.
     */
    public function synthesizeChordLoop(array $chords, float $chordDurationSec = 0.75, float $amplitude = 0.30): string
    {
        $pcm = '';
        $samplesPerChord = (int) round($chordDurationSec * self::SAMPLE_RATE);
        $fadeSamples = max(1, (int) round($samplesPerChord * 0.18));

        foreach ($chords as $chordFreqs) {
            $countNotes = max(1, count($chordFreqs));
            for ($i = 0; $i < $samplesPerChord; $i++) {
                $t = $i / self::SAMPLE_RATE;
                $env = 1.0;
                if ($i < $fadeSamples) {
                    $env = $i / $fadeSamples;
                } elseif ($i > $samplesPerChord - $fadeSamples) {
                    $env = max(0.0, ($samplesPerChord - $i) / $fadeSamples);
                }

                $sum = 0.0;
                foreach ($chordFreqs as $freq) {
                    $sum += sin(2.0 * M_PI * ((float) $freq) * $t);
                }

                $val = ($sum / $countNotes) * $amplitude * $env;
                $sampleInt = (int) max(-32767, min(32767, round($val * 32767)));
                $pcm .= pack('v', $sampleInt & 0xFFFF);
            }
        }

        return $this->wrapWavHeader($pcm, self::SAMPLE_RATE, 1, 16);
    }

    private function wrapWavHeader(string $pcmData, int $sampleRate, int $channels, int $bitsPerSample): string
    {
        $dataSize = strlen($pcmData);
        $byteRate = (int) ($sampleRate * $channels * ($bitsPerSample / 8));
        $blockAlign = (int) ($channels * ($bitsPerSample / 8));

        return 'RIFF'
            . pack('V', 36 + $dataSize)
            . 'WAVE'
            . 'fmt '
            . pack('V', 16)
            . pack('v', 1) // PCM format
            . pack('v', $channels)
            . pack('V', $sampleRate)
            . pack('V', $byteRate)
            . pack('v', $blockAlign)
            . pack('v', $bitsPerSample)
            . 'data'
            . pack('V', $dataSize)
            . $pcmData;
    }
}

