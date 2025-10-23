<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    protected $fillable = [
        'name',
        'code',
        'symbol',
        'symbol_position',
        'decimal_places',
        'is_base_currency',
        'is_active',
    ];

    protected $casts = [
        'is_base_currency' => 'boolean',
        'is_active' => 'boolean',
        'decimal_places' => 'integer',
    ];

    /**
     * Get exchange rates where this currency is the source
     */
    public function exchangeRatesFrom(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'from_currency_id');
    }

    /**
     * Get exchange rates where this currency is the target
     */
    public function exchangeRatesTo(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'to_currency_id');
    }

    /**
     * Get account groups using this currency
     */
    public function accountGroups(): HasMany
    {
        return $this->hasMany(AccountGroup::class);
    }

    /**
     * Get ledgers using this currency
     */
    public function ledgers(): HasMany
    {
        return $this->hasMany(Ledger::class);
    }

    /**
     * Format amount with currency symbol
     */
    public function formatAmount($amount): string
    {
        $formatted = number_format($amount, $this->decimal_places);
        
        if ($this->symbol_position === 'before') {
            return $this->symbol . ' ' . $formatted;
        }
        
        return $formatted . ' ' . $this->symbol;
    }

    /**
     * Scope to get active currencies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get base currency
     */
    public function scopeBase($query)
    {
        return $query->where('is_base_currency', true);
    }
}
