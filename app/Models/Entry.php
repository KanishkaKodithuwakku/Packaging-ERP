<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entry extends Model
{
    protected $fillable = [
        'tag_id',
        'entrytype_id',  // Changed to match Webzash column name
        'number',
        'date',
        'dr_total',
        'cr_total',
        'narration',
    ];

    protected $casts = [
        'date' => 'date',
        'dr_total' => 'decimal:2',
        'cr_total' => 'decimal:2',
        'number' => 'integer',
    ];

    /**
     * Get the tag
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    /**
     * Get the entry type
     */
    public function entryType(): BelongsTo
    {
        return $this->belongsTo(EntryType::class, 'entrytype_id');
    }

    /**
     * Get entry items
     */
    public function entryItems(): HasMany
    {
        return $this->hasMany(EntryItem::class);
    }

    /**
     * Get debit items
     */
    public function debitItems(): HasMany
    {
        return $this->hasMany(EntryItem::class)->where('dc', 'D');
    }

    /**
     * Get credit items
     */
    public function creditItems(): HasMany
    {
        return $this->hasMany(EntryItem::class)->where('dc', 'C');
    }

    /**
     * Get formatted entry number
     */
    public function getFormattedNumberAttribute(): string
    {
        return $this->entryType->formatNumber($this->number);
    }

    /**
     * Check if entry is balanced (dr_total == cr_total)
     */
    public function isBalanced(): bool
    {
        return bccomp($this->dr_total, $this->cr_total, 2) == 0;
    }

    /**
     * Calculate totals from entry items
     */
    public function calculateTotals(): void
    {
        $this->dr_total = $this->debitItems()->sum('amount');
        $this->cr_total = $this->creditItems()->sum('amount');
    }

    /**
     * Boot method to handle events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($entry) {
            if (!$entry->number && $entry->entryType->numbering == EntryType::NUMBERING_AUTO) {
                $entry->number = $entry->entryType->generateNextNumber();
            }
        });
    }
}
