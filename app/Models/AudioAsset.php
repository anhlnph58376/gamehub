<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class AudioAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'scope',
        'event_key',
        'file_path',
        'mime_type',
        'extension',
        'file_size',
        'duration_ms',
        'default_volume',
        'loop',
        'source_type',
        'license_name',
        'license_url',
        'usage_conditions',
        'author_attribution',
        'is_active',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'duration_ms' => 'integer',
            'default_volume' => 'float',
            'loop' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function gameAssignments(): HasMany
    {
        return $this->hasMany(GameAudioAsset::class, 'audio_asset_id');
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_audio_assets')
            ->withPivot(['sound_key', 'audio_type', 'volume', 'loop', 'preload', 'is_active'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeWebsiteScope(Builder $query): Builder
    {
        return $query->whereIn('scope', ['website', 'both']);
    }

    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        return asset('storage/' . ltrim($this->file_path, '/'));
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}

