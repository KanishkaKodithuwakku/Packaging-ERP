<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{
    protected $fillable = [
        'title',
        'color',
        'background',
    ];

    /**
     * Get entries with this tag
     */
    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    /**
     * Get style attribute for display
     */
    public function getStyleAttribute(): string
    {
        return "color: #{$this->color}; background-color: #{$this->background};";
    }
}
