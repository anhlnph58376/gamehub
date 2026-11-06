<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'description',
        'instructions',
        'controls',
        'author',
        'thumbnail_path',
        'banner_path',
        'category_id',
        'status',
        'orientation',
        'mobile_support',
        'play_count',
        'favorite_count',
        'review_count',
        'avg_rating',
        'is_featured',
        'is_hot',
        'is_new',
        'current_version_id',
        'uploaded_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'controls' => 'array',
            'mobile_support' => 'boolean',
            'play_count' => 'integer',
            'favorite_count' => 'integer',
            'review_count' => 'integer',
            'avg_rating' => 'float',
            'is_featured' => 'boolean',
            'is_hot' => 'boolean',
            'is_new' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'game_tag')->withTimestamps();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(GameVersion::class)->latest();
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class, 'current_version_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(GameFavorite::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(GameHistory::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(GameReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(GameReview::class)->where('status', 'approved')->latest();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(GameReport::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(GameScore::class)->orderByDesc('score');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_games')
            ->withPivot('order')
            ->withTimestamps();
    }

    public function gameAudioAssets(): HasMany
    {
        return $this->hasMany(GameAudioAsset::class);
    }

    public function audioAssets(): BelongsToMany
    {
        return $this->belongsToMany(AudioAsset::class, 'game_audio_assets')
            ->withPivot(['sound_key', 'audio_type', 'volume', 'loop', 'preload', 'is_active'])
            ->withTimestamps();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('current_version_id');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->published()->where('is_featured', true);
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail_path && str_starts_with($this->thumbnail_path, 'http')) {
            return $this->thumbnail_path;
        }

        if ($this->thumbnail_path) {
            return asset('storage/' . ltrim($this->thumbnail_path, '/'));
        }

        return asset('images/default-game-thumb.svg');
    }

    public function getPlayUrlAttribute(): ?string
    {
        $version = $this->currentVersion;
        if (!$version) {
            return null;
        }

        return $version->play_url;
    }

    public function recalculateRating(): void
    {
        $stats = $this->reviews()
            ->where('status', 'approved')
            ->selectRaw('COUNT(*) as total, COALESCE(AVG(rating), 0) as avg_score')
            ->first();

        $this->update([
            'review_count' => (int) ($stats->total ?? 0),
            'avg_rating' => round((float) ($stats->avg_score ?? 0), 2),
        ]);
    }
}
