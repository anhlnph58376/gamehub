<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'version_number',
        'storage_path',
        'entry_point',
        'zip_path',
        'file_size',
        'extracted_size',
        'file_count',
        'manifest_data',
        'compatibility_report',
        'is_compatible',
        'status',
        'is_active',
        'changelog',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'extracted_size' => 'integer',
            'file_count' => 'integer',
            'manifest_data' => 'array',
            'compatibility_report' => 'array',
            'is_compatible' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function files(): HasMany
    {
        return $this->hasMany(GameFile::class);
    }

    public function getPlayUrlAttribute(): string
    {
        $relativePath = trim($this->storage_path, '/') . '/' . ltrim($this->entry_point ?: 'index.html', '/');
        $sandboxDomain = rtrim((string) config('gamehub.sandbox_domain', ''), '/');

        if ($sandboxDomain !== '') {
            return $sandboxDomain . '/storage/' . $relativePath;
        }

        return asset('storage/' . $relativePath);
    }
}
