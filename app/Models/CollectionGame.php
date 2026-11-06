<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionGame extends Model
{
    protected $fillable = [
        'collection_id',
        'game_id',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}

