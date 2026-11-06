<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'points',
        'icon',
        'badge_path',
        'condition_type',
        'condition_value',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'condition_value' => 'integer',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot('earned_at')
            ->withTimestamps();
    }
}

