<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Food extends Model
{
    protected $table = 'foods';

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'available',
        'vendor_id',
        'preparation_minutes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'available' => 'boolean',
        'preparation_minutes' => 'integer',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return str_starts_with($this->image, 'http') ? $this->image : '/storage/'.ltrim($this->image, '/');
    }
}
