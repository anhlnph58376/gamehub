<?php

namespace Tests\Feature;

use App\Models\AudioAsset;
use App\Models\Category;
use App\Models\Game;
use App\Models\Role;
use App\Models\User;
use App\Services\Audio\AudioSynthesizerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AudioSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_audio_synthesizer_provisions_website_and_game_audio_assets(): void
    {
        Storage::fake('public');

        $category = Category::create(['name' => 'Arcade', 'slug' => 'arcade', 'is_active' => true]);
        $game = Game::create([
            'title' => 'Pixel Runner',
            'slug' => 'pixel-runner',
            'category_id' => $category->id,
            'status' => 'published',
        ]);

        $synth = app(AudioSynthesizerService::class);
        $synth->provisionAllDefaultAudio();

        $this->assertDatabaseHas('audio_assets', [
            'event_key' => 'ui.click',
            'category' => 'sfx',
            'scope' => 'website',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audio_assets', [
            'event_key' => 'bgm.home',
            'category' => 'music',
            'loop' => true,
        ]);

        $this->assertDatabaseHas('game_audio_assets', [
            'game_id' => $game->id,
            'sound_key' => 'bgm_main',
            'audio_type' => 'music',
        ]);

        $this->assertDatabaseHas('game_audio_assets', [
            'game_id' => $game->id,
            'sound_key' => 'sfx_jump',
            'audio_type' => 'sfx',
        ]);

        Storage::disk('public')->assertExists('audio/website/sfx/ui-click.wav');
        Storage::disk('public')->assertExists('audio/website/music/bgm-home.wav');
        Storage::disk('public')->assertExists('games/pixel-runner/assets/audio/music/runner-bgm.wav');
        Storage::disk('public')->assertExists('games/pixel-runner/assets/audio/sfx/jump.wav');
        Storage::disk('public')->assertExists('games/pixel-runner/assets/audio/ambient/cyber-wind.wav');
    }

    public function test_guest_and_authenticated_user_can_load_and_update_global_audio_settings(): void
    {
        Storage::fake('public');
        app(AudioSynthesizerService::class)->provisionAllDefaultAudio();

        // Guest request
        $guestRes = $this->getJson('/api/audio-settings');
        $guestRes->assertStatus(200)
            ->assertJsonPath('authenticated', false)
            ->assertJsonStructure([
                'authenticated',
                'settings' => [
                    'master_volume',
                    'music_volume',
                    'ui_volume',
                    'game_volume',
                    'mute_all',
                    'hover_sound_enabled',
                    'bgm_enabled',
                ],
                'manifest',
            ]);

        // Authenticated user saves custom audio settings
        $user = User::factory()->create(['is_active' => true]);

        $updateRes = $this->actingAs($user)->postJson('/api/audio-settings', [
            'master_volume' => 0.65,
            'music_volume' => 0.35,
            'ui_volume' => 0.50,
            'game_volume' => 0.90,
            'mute_all' => true,
            'hover_sound_enabled' => false,
            'bgm_enabled' => true,
        ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('persisted_to_account', true)
            ->assertJsonPath('settings.master_volume', 0.65)
            ->assertJsonPath('settings.mute_all', true);

        $this->assertDatabaseHas('user_audio_settings', [
            'user_id' => $user->id,
            'master_volume' => 0.65,
            'music_volume' => 0.35,
            'mute_all' => 1,
            'hover_sound_enabled' => 0,
        ]);
    }

    public function test_game_audio_manifest_endpoint_returns_game_specific_sounds(): void
    {
        Storage::fake('public');

        $category = Category::create(['name' => 'Arcade', 'slug' => 'arcade', 'is_active' => true]);
        $game = Game::create([
            'title' => 'Space Shooter',
            'slug' => 'space-shooter',
            'category_id' => $category->id,
            'status' => 'published',
        ]);

        app(AudioSynthesizerService::class)->provisionAllDefaultAudio();

        $res = $this->getJson('/api/games/space-shooter/audio-manifest');
        $res->assertStatus(200)
            ->assertJsonPath('game_slug', 'space-shooter')
            ->assertJsonStructure([
                'game_slug',
                'sounds' => [
                    'bgm_main' => ['sound_key', 'type', 'url', 'volume', 'loop', 'preload', 'license'],
                    'sfx_laser',
                    'sfx_explosion',
                    'ambient_space',
                ],
            ]);
    }

    public function test_admin_can_upload_valid_wav_audio_and_assign_to_game_while_blocking_executables(): void
    {
        Storage::fake('public');

        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $admin = User::factory()->create(['is_active' => true]);
        $admin->roles()->attach($adminRole->id);

        $category = Category::create(['name' => 'Puzzle', 'slug' => 'puzzle', 'is_active' => true]);
        $game = Game::create([
            'title' => 'Puzzle Blocks',
            'slug' => 'puzzle-blocks',
            'category_id' => $category->id,
            'status' => 'published',
        ]);

        $synth = app(AudioSynthesizerService::class);
        $validWavBytes = $synth->synthesizeToneSequence([
            ['freq' => 440.0, 'end_freq' => 880.0, 'duration' => 0.1, 'amp' => 0.5],
        ]);

        $validFile = UploadedFile::fake()->createWithContent('victory-chime.wav', $validWavBytes);

        $res = $this->actingAs($admin)->post(route('admin.audio.store'), [
            'title' => 'Victory Fanfare Chime',
            'description' => 'Âm thanh chiến thắng màn chơi.',
            'category' => 'sfx',
            'scope' => 'game',
            'event_key' => 'game.victory',
            'default_volume' => 0.85,
            'loop' => 0,
            'source_type' => 'project_generated',
            'license_name' => 'CC0 1.0 Public Domain',
            'author_attribution' => 'GameHub Sound Team',
            'game_id' => $game->id,
            'sound_key' => 'sfx_victory',
            'audio_file' => $validFile,
        ]);

        $res->assertRedirect();
        $this->assertDatabaseHas('audio_assets', [
            'title' => 'Victory Fanfare Chime',
            'category' => 'sfx',
            'license_name' => 'CC0 1.0 Public Domain',
        ]);

        $this->assertDatabaseHas('game_audio_assets', [
            'game_id' => $game->id,
            'sound_key' => 'sfx_victory',
        ]);

        // Attempt to upload a file with forbidden double extension (.php.wav)
        $forbiddenFile = UploadedFile::fake()->createWithContent('evil.php.wav', $validWavBytes);
        $badRes = $this->actingAs($admin)->post(route('admin.audio.store'), [
            'title' => 'Invalid Audio',
            'category' => 'sfx',
            'scope' => 'website',
            'default_volume' => 0.5,
            'source_type' => 'project_generated',
            'license_name' => 'CC0',
            'audio_file' => $forbiddenFile,
        ]);

        $badRes->assertRedirect();
        $badRes->assertSessionHas('error');
        $this->assertDatabaseMissing('audio_assets', ['title' => 'Invalid Audio']);

        // Attempt to upload a fake WAV file with invalid magic bytes
        $fakeWav = UploadedFile::fake()->createWithContent('fake.wav', 'NOT_A_VALID_RIFF_WAVE_FILE');
        $fakeRes = $this->actingAs($admin)->post(route('admin.audio.store'), [
            'title' => 'Fake Magic Bytes Audio',
            'category' => 'sfx',
            'scope' => 'website',
            'default_volume' => 0.5,
            'source_type' => 'project_generated',
            'license_name' => 'CC0',
            'audio_file' => $fakeWav,
        ]);

        $fakeRes->assertRedirect();
        $fakeRes->assertSessionHas('error');
        $this->assertDatabaseMissing('audio_assets', ['title' => 'Fake Magic Bytes Audio']);
    }

    public function test_all_admin_and_user_views_render_without_errors(): void
    {
        Storage::fake('public');

        $adminRole = Role::create(['name' => 'Super Admin', 'slug' => 'super-admin']);
        $admin = User::factory()->create(['is_active' => true]);
        $admin->roles()->attach($adminRole->id);

        Category::create(['name' => 'Arcade', 'slug' => 'arcade', 'position' => 1, 'is_active' => true]);
        app(AudioSynthesizerService::class)->provisionAllDefaultAudio($admin->id);

        $routes = [
            route('home'),
            route('games.explore'),
            route('games.category', 'arcade'),
            route('user.profile'),
            route('user.favorites'),
            route('user.history'),
            route('admin.dashboard'),
            route('admin.games.index'),
            route('admin.games.create'),
            route('admin.audio.index'),
            route('admin.categories.index'),
            route('admin.tags.index'),
            route('admin.users.index'),
            route('admin.reviews.index'),
            route('admin.reports.index'),
            route('admin.cms.index'),
            route('admin.settings.index'),
            route('admin.audit-logs.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($admin)->get($url)->assertStatus(200);
        }
    }
}
