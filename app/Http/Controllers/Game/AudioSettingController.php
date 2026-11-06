<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\AudioAsset;
use App\Models\Game;
use App\Models\UserAudioSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AudioSettingController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $defaults = [
            'master_volume' => 0.80,
            'music_volume' => 0.50,
            'ui_volume' => 0.70,
            'game_volume' => 0.85,
            'mute_all' => false,
            'hover_sound_enabled' => true,
            'bgm_enabled' => true,
        ];

        $settings = $defaults;

        if ($request->user()) {
            $record = UserAudioSetting::firstOrCreate(
                ['user_id' => $request->user()->id],
                $defaults
            );

            $settings = [
                'master_volume' => (float) $record->master_volume,
                'music_volume' => (float) $record->music_volume,
                'ui_volume' => (float) $record->ui_volume,
                'game_volume' => (float) $record->game_volume,
                'mute_all' => (bool) $record->mute_all,
                'hover_sound_enabled' => (bool) $record->hover_sound_enabled,
                'bgm_enabled' => (bool) $record->bgm_enabled,
            ];
        }

        $websiteAssets = AudioAsset::active()
            ->websiteScope()
            ->whereNotNull('event_key')
            ->get()
            ->mapWithKeys(fn (AudioAsset $asset) => [
                $asset->event_key => [
                    'id' => $asset->id,
                    'title' => $asset->title,
                    'category' => $asset->category,
                    'url' => $asset->url,
                    'volume' => (float) $asset->default_volume,
                    'loop' => (bool) $asset->loop,
                ],
            ]);

        return response()->json([
            'authenticated' => (bool) $request->user(),
            'settings' => $settings,
            'manifest' => $websiteAssets,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'master_volume' => ['required', 'numeric', 'min:0', 'max:1'],
            'music_volume' => ['required', 'numeric', 'min:0', 'max:1'],
            'ui_volume' => ['required', 'numeric', 'min:0', 'max:1'],
            'game_volume' => ['required', 'numeric', 'min:0', 'max:1'],
            'mute_all' => ['required', 'boolean'],
            'hover_sound_enabled' => ['required', 'boolean'],
            'bgm_enabled' => ['required', 'boolean'],
        ]);

        $normalized = [
            'master_volume' => round((float) $validated['master_volume'], 2),
            'music_volume' => round((float) $validated['music_volume'], 2),
            'ui_volume' => round((float) $validated['ui_volume'], 2),
            'game_volume' => round((float) $validated['game_volume'], 2),
            'mute_all' => (bool) $validated['mute_all'],
            'hover_sound_enabled' => (bool) $validated['hover_sound_enabled'],
            'bgm_enabled' => (bool) $validated['bgm_enabled'],
        ];

        if ($request->user()) {
            UserAudioSetting::updateOrCreate(
                ['user_id' => $request->user()->id],
                $normalized
            );
        }

        return response()->json([
            'status' => 'success',
            'persisted_to_account' => (bool) $request->user(),
            'settings' => $normalized,
        ]);
    }

    public function gameManifest(string $slug): JsonResponse
    {
        $game = Game::where('slug', $slug)->firstOrFail();

        $assignments = $game->gameAudioAssets()
            ->where('is_active', true)
            ->with('audioAsset')
            ->get()
            ->filter(fn ($item) => $item->audioAsset && $item->audioAsset->is_active)
            ->mapWithKeys(fn ($item) => [
                $item->sound_key => [
                    'sound_key' => $item->sound_key,
                    'type' => $item->audio_type,
                    'url' => $item->audioAsset->url,
                    'volume' => (float) $item->volume,
                    'loop' => (bool) $item->loop,
                    'preload' => (bool) $item->preload,
                    'license' => $item->audioAsset->license_name,
                ],
            ]);

        return response()->json([
            'game_slug' => $game->slug,
            'sounds' => $assignments,
        ]);
    }
}

