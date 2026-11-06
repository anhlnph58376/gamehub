<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'is_active',
        'locked_at',
        'total_points',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'locked_at' => 'datetime',
            'is_active' => 'boolean',
            'total_points' => 'integer',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(GameFavorite::class);
    }

    public function favoriteGames(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_favorites')->withTimestamps();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(GameHistory::class)->latest('played_at');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(GameReview::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(GameReport::class);
    }

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public function scores(): HasMany
    {
        return $this->hasMany(GameScore::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function audioSetting(): HasOne
    {
        return $this->hasOne(UserAudioSetting::class);
    }

    public function hasRole(string|array $roleSlug): bool
    {
        $slugs = is_array($roleSlug) ? $roleSlug : [$roleSlug];

        return $this->roles->whereIn('slug', $slugs)->isNotEmpty();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(['super-admin', 'admin']);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->permissions->contains('slug', $permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        if ($this->avatar) {
            return asset('storage/' . ltrim($this->avatar, '/'));
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=7C5CFF&color=fff&bold=true';
    }
}
