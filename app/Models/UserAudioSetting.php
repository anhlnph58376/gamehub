<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAudioSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'master_volume',
        'music_volume',
        'ui_volume',
        'game_volume',
        'mute_all',
        'hover_sound_enabled',
        'bgm_enabled',
    ];

    protected function casts(): array
    {
        return [
            'master_volume' => 'float',
            'music_volume' => 'float',
            'ui_volume' => 'float',
            'game_volume' => 'float',
            'mute_all' => 'boolean',
            'hover_sound_enabled' => 'boolean',
            'bgm_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

