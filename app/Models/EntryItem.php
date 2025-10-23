<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntryItem extends Model
{
    protected $table = 'entryitems';  // Match Webzash table name
    
    protected $fillable = [
        'entry_id',
        'ledger_id',
        'amount',
        'dc',
        'reconciliation_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'reconciliation_date' => 'date',
    ];

    /**
     * Get the entry
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    /**
     * Get the ledger
     */
    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    /**
     * Check if item is debit
     */
    public function isDebit(): bool
    {
        return $this->dc === 'D';
    }

    /**
     * Check if item is credit
     */
    public function isCredit(): bool
    {
        return $this->dc === 'C';
    }

    /**
     * Check if item is reconciled
     */
    public function isReconciled(): bool
    {
        return !is_null($this->reconciliation_date);
    }

    /**
     * Scope for unreconciled items
     */
    public function scopeUnreconciled($query)
    {
        return $query->whereNull('reconciliation_date');
    }

    /**
     * Scope for reconciled items
     */
    public function scopeReconciled($query)
    {
        return $query->whereNotNull('reconciliation_date');
    }

    /**
     * Scope for debit items
     */
    public function scopeDebit($query)
    {
        return $query->where('dc', 'D');
    }

    /**
     * Scope for credit items
     */
    public function scopeCredit($query)
    {
        return $query->where('dc', 'C');
    }
}
