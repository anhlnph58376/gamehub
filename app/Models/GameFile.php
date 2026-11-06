<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_version_id',
        'file_path',
        'type',
        'mime_type',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class, 'game_version_id');
    }
}
