<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameAudioAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'audio_asset_id',
        'sound_key',
        'audio_type',
        'volume',
        'loop',
        'preload',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'volume' => 'float',
            'loop' => 'boolean',
            'preload' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function audioAsset(): BelongsTo
    {
        return $this->belongsTo(AudioAsset::class);
    }
}

