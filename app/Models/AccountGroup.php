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

    /**
     * Calculate opening balance for this group and all its children
     */
    public function calculateOpeningBalance(): float
    {
        $openingTotal = 0;

        // Calculate opening balance from direct ledgers
        foreach ($this->ledgers as $ledger) {
            $openingTotal = bcadd($openingTotal, $ledger->op_balance, 2);
        }

        // Calculate opening balance from child groups recursively
        foreach ($this->children as $childGroup) {
            $openingTotal = bcadd($openingTotal, $childGroup->calculateOpeningBalance(), 2);
        }

        return $openingTotal;
    }

    /**
     * Calculate closing balance for this group and all its children
     */
    public function calculateClosingBalance($startDate = null, $endDate = null): array
    {
        $debitTotal = 0;
        $creditTotal = 0;

        // Calculate balance from direct ledgers
        foreach ($this->ledgers as $ledger) {
            $balance = $ledger->closingBalance($startDate, $endDate);
            if ($balance['dc'] == 'D') {
                $debitTotal = bcadd($debitTotal, $balance['amount'], 2);
            } else {
                $creditTotal = bcadd($creditTotal, $balance['amount'], 2);
            }
        }

        // Calculate balance from child groups recursively
        foreach ($this->children as $childGroup) {
            $childBalance = $childGroup->calculateClosingBalance($startDate, $endDate);
            $debitTotal = bcadd($debitTotal, $childBalance['debit'], 2);
            $creditTotal = bcadd($creditTotal, $childBalance['credit'], 2);
        }

        return [
            'debit' => $debitTotal,
            'credit' => $creditTotal
        ];
    }
}
