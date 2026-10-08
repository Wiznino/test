<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    protected $fillable = ['title', 'description', 'image', 'food_id', 'starts_at', 'ends_at', 'is_active'];

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean'];
    }

    public function scopeCurrentlyVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereNotNull('food_id')
            ->whereNotNull('image')
            ->whereHas('food', fn (Builder $foodQuery) => $foodQuery->where('available', true))
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? '/storage/'.ltrim($this->image, '/') : null;
    }
}
