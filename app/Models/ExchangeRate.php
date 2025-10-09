<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'rate',
        'rate_date',
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'rate_date' => 'date',
    ];

    /**
     * Get the source currency
     */
    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    /**
     * Get the target currency
     */
    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    /**
     * Get exchange rate for a specific date
     */
    public static function getRate($fromCurrencyId, $toCurrencyId, $date)
    {
        $rate = self::where('from_currency_id', $fromCurrencyId)
            ->where('to_currency_id', $toCurrencyId)
            ->where('rate_date', '<=', $date)
            ->orderBy('rate_date', 'desc')
            ->first();

        return $rate ? $rate->rate : 1;
    }

    /**
     * Convert amount from one currency to another
     */
    public static function convert($amount, $fromCurrencyId, $toCurrencyId, $date)
    {
        if ($fromCurrencyId == $toCurrencyId) {
            return $amount;
        }

        $rate = self::getRate($fromCurrencyId, $toCurrencyId, $date);
        return $amount * $rate;
    }
}
