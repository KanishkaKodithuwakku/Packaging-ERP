<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Ledger extends Model
{
    protected $fillable = [
        'group_id',
        'name',
        'code',
        'op_balance',
        'op_balance_dc',
        'type',
        'reconciliation',
        'notes',
        'currency_id',
    ];

    protected $casts = [
        'op_balance' => 'decimal:2',
        'type' => 'integer',
        'reconciliation' => 'boolean',
    ];

    const TYPE_NORMAL = 0;
    const TYPE_BANK_CASH = 1;

    /**
     * Get the account group
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class, 'group_id');
    }

    /**
     * Get the currency
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get entry items for this ledger
     */
    public function entryItems(): HasMany
    {
        return $this->hasMany(EntryItem::class);
    }

    /**
     * Calculate opening balance for a given date range
     */
    public function openingBalance($startDate = null): array
    {
        $opTotal = $this->op_balance ?? 0;
        $opTotalDc = $this->op_balance_dc;

        if (is_null($startDate)) {
            return ['dc' => $opTotalDc, 'amount' => $opTotal];
        }

        // Calculate debit total before start date
        $drTotal = $this->entryItems()
            ->where('dc', 'D')
            ->whereHas('entry', function ($query) use ($startDate) {
                $query->where('date', '<', $startDate);
            })
            ->sum('amount');

        // Calculate credit total before start date
        $crTotal = $this->entryItems()
            ->where('dc', 'C')
            ->whereHas('entry', function ($query) use ($startDate) {
                $query->where('date', '<', $startDate);
            })
            ->sum('amount');

        // Add opening balance
        if ($opTotalDc == 'D') {
            $drTotalFinal = bcadd($opTotal, $drTotal, 2);
            $crTotalFinal = $crTotal;
        } else {
            $drTotalFinal = $drTotal;
            $crTotalFinal = bcadd($opTotal, $crTotal, 2);
        }

        // Calculate final opening balance
        if (bccomp($drTotalFinal, $crTotalFinal, 2) > 0) {
            $opTotal = bcsub($drTotalFinal, $crTotalFinal, 2);
            $opTotalDc = 'D';
        } elseif (bccomp($drTotalFinal, $crTotalFinal, 2) == 0) {
            $opTotal = 0;
            // $opTotalDc remains the same
        } else {
            $opTotal = bcsub($crTotalFinal, $drTotalFinal, 2);
            $opTotalDc = 'C';
        }

        return ['dc' => $opTotalDc, 'amount' => $opTotal];
    }

    /**
     * Calculate closing balance for a given date range
     */
    public function closingBalance($startDate = null, $endDate = null): array
    {
        $op = $this->openingBalance($startDate);
        $opTotal = $startDate ? 0 : ($op['amount'] ?? 0);
        $opTotalDc = $op['dc'];

        // Calculate debit total in date range
        $drQuery = $this->entryItems()->where('dc', 'D');
        if (!is_null($startDate)) {
            $drQuery->whereHas('entry', function ($query) use ($startDate) {
                $query->where('date', '>=', $startDate);
            });
        }
        if (!is_null($endDate)) {
            $drQuery->whereHas('entry', function ($query) use ($endDate) {
                $query->where('date', '<=', $endDate);
            });
        }
        $drTotal = $drQuery->sum('amount');

        // Calculate credit total in date range
        $crQuery = $this->entryItems()->where('dc', 'C');
        if (!is_null($startDate)) {
            $crQuery->whereHas('entry', function ($query) use ($startDate) {
                $query->where('date', '>=', $startDate);
            });
        }
        if (!is_null($endDate)) {
            $crQuery->whereHas('entry', function ($query) use ($endDate) {
                $query->where('date', '<=', $endDate);
            });
        }
        $crTotal = $crQuery->sum('amount');

        // Add opening balance
        if ($opTotalDc == 'D') {
            $drTotalDc = bcadd($opTotal, $drTotal, 2);
            $crTotalDc = $crTotal;
        } else {
            $drTotalDc = $drTotal;
            $crTotalDc = bcadd($opTotal, $crTotal, 2);
        }

        // Calculate and return closing balance
        if (bccomp($drTotalDc, $crTotalDc, 2) > 0) {
            $cl = bcsub($drTotalDc, $crTotalDc, 2);
            $clDc = 'D';
        } elseif (bccomp($drTotalDc, $crTotalDc, 2) == 0) {
            $cl = 0;
            $clDc = $opTotalDc;
        } else {
            $cl = bcsub($crTotalDc, $drTotalDc, 2);
            $clDc = 'C';
        }

        return [
            'dc' => $clDc,
            'amount' => $cl,
            'dr_total' => $drTotal,
            'cr_total' => $crTotal
        ];
    }

    /**
     * Get display name with code
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->code) {
            return $this->code . ' - ' . $this->name;
        }
        return $this->name;
    }

    /**
     * Scope for bank/cash accounts
     */
    public function scopeBankCash($query)
    {
        return $query->where('type', self::TYPE_BANK_CASH);
    }

    /**
     * Check if this is a bank/cash account
     */
    public function isBankCash(): bool
    {
        return $this->type == self::TYPE_BANK_CASH;
    }

    /**
     * Calculate closing balance (alias for closingBalance method)
     */
    public function calculateClosingBalance($startDate = null, $endDate = null): array
    {
        $balance = $this->closingBalance($startDate, $endDate);
        return [
            'debit' => $balance['dc'] == 'D' ? $balance['amount'] : 0,
            'credit' => $balance['dc'] == 'C' ? $balance['amount'] : 0
        ];
    }
}
