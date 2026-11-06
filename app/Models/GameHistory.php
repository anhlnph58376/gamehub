<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game_id',
        'played_at',
        'duration_seconds',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'played_at' => 'datetime',
            'duration_seconds' => 'integer',
            'score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
