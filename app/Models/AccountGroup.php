<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountGroup extends Model
{
    protected $table = 'groups';  // Match Webzash table name
    
    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'affects_gross',
        'currency_id',
    ];

    protected $casts = [
        'affects_gross' => 'boolean',
        'parent_id' => 'integer',
    ];

    /**
     * Get the parent group
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class, 'parent_id');
    }

    /**
     * Get child groups
     */
    public function children(): HasMany
    {
        return $this->hasMany(AccountGroup::class, 'parent_id');
    }

    /**
     * Get all child groups recursively
     */
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Get the currency for this group
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get ledgers in this group
     */
    public function ledgers(): HasMany
    {
        return $this->hasMany(Ledger::class, 'group_id');
    }

    /**
     * Get full path of group names
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * Check if group has children
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Check if group has ledgers
     */
    public function hasLedgers(): bool
    {
        return $this->ledgers()->count() > 0;
    }

    /**
     * Get the level/depth of this group in the hierarchy
     */
    public function getLevel(): int
    {
        $level = 0;
        $parent = $this->parent;

        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }

        return $level;
    }

    /**
     * Scope to get root groups (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id')->orWhere('parent_id', 0);
    }
}
